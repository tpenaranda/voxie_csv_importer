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
                        <b-form-select v-model="fieldsMapping[field.key]" :options="parsedFilePreviewData[0].map((v, k) => { return {text: v, value: k} })"></b-form-select>
                    </td>
                </tr>
                </tbody>
            </table>
            <b-spinner v-if="requestInProgress" variant="warning" label="Uploading..."></b-spinner>
            <b-button v-else @click.prevent="postData" :disabled="false" class="btn btn-warning">Upload data</b-button>
        </div>

        <div class="step-three" v-if="step === 3">
            <p class="text-success success-message">All good! This is the data returning from the BE tables...</p>
            <b-table striped bordered small :items="results.items" :fields="results.fields.map((i) => i.key) "></b-table>
        </div>

        <div class="errors-modal">
          <b-modal id="errors-modal" scrollable centered :title="errorTitle || 'Can\'t store your data!'" ok-only>
            <b-list-group>
                <b-list-group-item v-for="(error, index) in errors" v-bind:key="index" variant="warning">
                    {{ error }}
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
            rowLimit: 100,
            errors: [],
            errorTitle: null,
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
            parsedFileData: null,
            parsedFilePreviewData: null
        }),
        created () {
        },
        methods: {
            parseFile() {
                let reader = new FileReader();
                reader.readAsText(this.csvFile, "UTF-8");

                reader.onload = (evt) => {
                    let csvString = evt.target.result;

                    this.parsedFilePreviewData = Papaparse.parse(csvString, { preview: 2, skipEmptyLines: true }).data

                    let parsedFile = Papaparse.parse(csvString, { preview: this.rowLimit, skipEmptyLines: true })
                    this.parsedFileData = parsedFile.data

                    if (parsedFile.meta.truncated) {
                        this.errorTitle = 'Data truncated!'
                        this.errors = [`Your CSV file is being truncated to ${this.rowLimit} rows. This importer tool works synchronically against the BE (no Jobs/Queues) so intensive usage of CPU/Database could translate in timeouts or blocked FE.`]
                        this.$bvModal.show('errors-modal')
                    }

                    this.step = 2
                };

                reader.onerror = function () {
                    alert('Error reading file.')
                };
            },
            postData() {
                this.requestInProgress = true
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
                        this.errors = []
                        _.forEach(error.response.data.errors, (error) => {
                            let errorPieces = error[0].split('.', 3)
                            this.errors.push(`Row #${errorPieces[1]}: ${errorPieces[2]}`)
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
                let data = _.clone(this.parsedFileData)

                data.shift()

                return _.map(data, (row) => {
                    let newRow = {};

                    _.forEach(this.fieldsMapping, (column, fieldName) => {
                        _.set(newRow, fieldName, _.get(row, column));
                    })

                    _.forEach(row.filter((data, column) => !mappedColumns.includes(column)), (data, column) => {
                        _.set(newRow, `Custom #${column}`, _.get(row, column));
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
        width: 85%;
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
