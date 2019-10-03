<template>
    <div class="main">
        <img src="https://www.voxie.com/wp-content/uploads/elementor/thumbs/VOXIE-black-o1ol2ora2qld6vodwmsrb0qd5dj7ere5midef05xk8.png" width="400">
        <h2>CSV File Parser</h2>

        <b-progress :max="3" class="step-bar">
          <b-progress-bar :value="step" :label="`Step ${step} / 3`"></b-progress-bar>
        </b-progress>

        <div class="step-one" v-if="step === 1">
            <b-form-file
                v-model="csvFile"
                :state="!!csvFile"
                placeholder="Choose a CSV file (with header) or drop it here..."
                drop-placeholder="Drop file here..."
                accept="text/csv, text/x-csv, application/vnd.ms-excel, text/plain"
                class="csv-input"
            ></b-form-file>

            <b-button @click.prevent="parseFile" :disabled="!csvFile" class="btn btn-success">Parse file</b-button>
        </div>

        <div class="step-two" v-if="step === 2">
            <table class="table-condensed borderless">
                <thead>
                <tr>
                    <th>Field</th>
                    <th>CSV Column</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(field, key) in results.fields.filter((i) => i.label)" :key="key">
                    <td v-bind:class="{'text-danger': field.required}">{{ field.label }}</td>
                    <td>
                        <b-form-select v-model="fieldsMapping[field.key]" :options="parsedFilePreview[0].map((v, k) => { return {text: v, value: k} })"></b-form-select>
                    </td>
                </tr>
                </tbody>
            </table>
            <b-button @click.prevent="postData" :disabled="false" class="btn btn-warning">Upload data</b-button>
        </div>

        <div class="step-three" v-if="step === 3">
            <b-table striped hover :items="results.items" :fields="results.fields"></b-table>
        </div>

        <div class="errors-modal">
          <b-modal id="errors-modal" scrollable centered title="Can't store your data!" ok-only>
            <b-list-group>
                <b-list-group-item v-for="(error, index) in errors" v-bind:key="index" variant="warning">
                    <strong>Error at row #{{ error[1] }}:</strong> {{ error[2] }}
                </b-list-group-item>
            </b-list-group>
          </b-modal>
        </div>
    </div>
</template>

<script>
    import Papaparse from 'papaparse';

    export default {
        name: 'MainComponent',
        data: () => ({
            csvFile: null,
            errors: [],
            fieldsMapping: {},
            results: {
                fields: [
                    { key: 'email', sorteable: true, label: 'Email [string, unique]' },
                    { key: 'fb_messenger_id', sorteable: true, label: 'Facebook Messenger #ID [string]' },
                    { key: 'first_name', sorteable: true, label: 'First Name [string]' },
                    { key: 'last_name', sorteable: true, label: 'Last Name [string]' },
                    { key: 'phone', sorteable: false, label: 'Phone [required, string]', required: true },
                    { key: 'sticky_phone_number_id', sorteable: false, label: 'Sticky Phone Number #ID [integer]' },
                    { key: 'team_id', sorteable: false, label: 'Team #ID [required, integer]', required: true },
                    { key: 'time_zone', sorteable: false, label: 'Time Zone [string]' },
                    { key: 'twitter_id', sorteable: true, label: 'Twitter #ID [string]' },
                    { key: 'unsubscribed_status', sorteable: true, label: 'Unsubscribed Status [required, string]', required: true },
                    { key: 'custom_attributes', sorteable: false, variant: 'secondary'}
                ],
                items: []
            },
            requestInProgress: false,
            step: 1,
            parsedFile: null,
            parsedFilePreview: null
        }),
        created () {
        },
        methods: {
            parseFile() {
                let reader = new FileReader();
                reader.readAsText(this.csvFile, "UTF-8");

                reader.onload = (evt) => {
                    let data = evt.target.result;

                    this.parsedFilePreview = _.get(Papaparse.parse(data, { preview: 2, skipEmptyLines: true }), 'data')
                    this.parsedFile = _.get(Papaparse.parse(data, { skipEmptyLines: true }), 'data')

                    this.step = 2
                };

                reader.onerror = function () {
                    alert('Error reading file.')
                };
            },
            postData() {
                this.requestInProgress = true
                this.errors = []
                axios.post('/api/contacts', {data: this.buildPostData()}).then((response) => {
                    this.results.items = _.map(response.data, (row) => {
                        row.custom_attributes = _.map(row.custom_attributes, (item) => {
                            return `${item.key}: ${item.value}`
                        }).join(' / ')
                        return row
                    })
                    this.step = 3
                }).catch(error => {
                    if (error.response && error.response.status === 422) {
                        _.forEach(error.response.data.errors, (error) => {
                            this.errors.push(error[0].split('.', 3))
                        })
                        this.$bvModal.show('errors-modal')
                    } else {
                        alert('Unknown error when submitting CSV data...')
                    }
                }).finally((response) => {
                    this.requestInProgress = false
                })
            },
            buildPostData() {
                let mappedColumns = Object.values(this.fieldsMapping)
                let data = _.clone(this.parsedFile)

                data.shift()

                return _.map(data, (row) => {
                    let newRow = {};

                    _.forEach(this.fieldsMapping, (column, fieldName) => {
                        _.set(newRow, fieldName, _.get(row, column));
                    })

                    _.forEach(row.filter((data, column) => !mappedColumns.includes(column)), (data, column) => {
                        _.set(newRow, `Column #${column}`, _.get(row, column));
                    })

                    return newRow;
                });
            }
        }
    }
</script>

<style lang="scss">
    h2 {
        border-bottom: 1px solid;
        border-top: 1px solid;
        margin-top: .25em;
        margin-bottom: .25em;
    }
    table {
        margin-bottom: .75em;
    }
    .step-one {
        display: contents;
    }
    .step-two {
        margin: 1em;
    }
    .step-three {
        width: 95%;
        margin-top: 1em;
    }
    .step-bar {
        width: 85%;
        margin-top: 5em;
    }
    .csv-input {
        width: 65%;
        margin-top: 5em;
        margin-bottom: 3em;
    }
    .main {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      min-height: 100vh;
      border: 1px solid;
    }
</style>
