<template>
    <div class="main">
        <b-container v-if="$root.step === 1" class="text-center p-5 mt-4">
            <b-row>
                <b-col align-self="center" class="mx-5">
                    <b-form-file
                        v-model="csvFile"
                        :state="!!csvFile"
                        placeholder="Choose a CSV file..."
                        drop-placeholder="Drop CSV file here..."
                        accept="text/csv, text/x-csv, application/vnd.ms-excel, text/plain"
                        class="csv-input"
                    ></b-form-file>
                    <b-button @click="parseFile" :disabled="!csvFile" class="btn btn-success mt-2 px-5">Next</b-button>
                </b-col>
            </b-row>
        </b-container>

        <b-container v-if="$root.step === 2" class="my-3">
            <b-container class="field-mapping text-center">
                <b-row class="m-2 text-center" >
                    <b-col><strong>Field</strong></b-col>
                    <b-col><strong>CSV Column</strong></b-col>
                </b-row>
                <b-row v-for="(field, key) in fields.filter((i) => i.label)" :key="key" class="p-3 border-top">
                    <b-col align-self="center">
                        <p v-bind:class="{'text-danger': field.required, 'text-right': true, 'm-0': true, 'pr-2': true}">
                            {{ field.label }}
                        </p>
                    </b-col>
                    <b-col align-self="center">
                        <b-form-select v-model="fieldsMapping[field.key]" :options="parsedFilePreviewData[0].map((v, k) => { return {text: v, value: k} })"></b-form-select>
                    </b-col>
                </b-row>
                <b-spinner v-if="requestInProgress" variant="warning" class="mt-2" label="Uploading..."></b-spinner>
                <b-button v-else @click="postData" class="btn btn-success mt-2 px-5">Next</b-button>
            </b-container>
        </b-container>

        <b-container v-if="$root.step === 3" class="results my-3 text-center" fluid>
            <p class="text-success success-message">All good! This is the data returning from the BE tables...</p>
            <b-row cols="12">
                <b-col v-for="(column, index) in results.columns" v-bind:key="index" class="text-capitalize">
                    <strong>{{ column.replace(/_/g, ' ') }}</strong>
                </b-col>
            </b-row>
            <b-row v-for="row in results.rows" v-bind:key="row.id" cols="12" class="border">
                <b-col v-for="(column, index) in results.columns" v-bind:key="index">
                    <span v-if="column === 'custom_attributes'">
                        <span v-for="custom_attribute in row[column]">
                            <p class="custom-attribute">{{ custom_attribute.key }}: {{ custom_attribute.value }}</p>
                        </span>
                    </span>
                    <span v-else>{{ row[column] }}</span>
                </b-col>
            </b-row>
        </b-container>

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
        data() {
            return {
                csvFile: null,
                errors: [],
                errorTitle: null,
                fields: [
                    { key: 'email', label: 'Email [string, unique]' },
                    { key: 'fb_messenger_id', label: 'Facebook Messenger #ID [string]' },
                    { key: 'first_name', label: 'First Name [string]' },
                    { key: 'last_name', label: 'Last Name [string]' },
                    { key: 'phone', label: 'Phone [required, string]', required: true },
                    { key: 'sticky_phone_number_id', label: 'Sticky Phone Number #ID [integer]' },
                    { key: 'team_id', label: 'Team #ID [required, integer]', required: true },
                    { key: 'time_zone', label: 'Time Zone [string]' },
                    { key: 'twitter_id', label: 'Twitter #ID [string]' },
                    { key: 'unsubscribed_status', label: 'Unsubscribed Status [required, string]', required: true },
                    { key: 'custom_attributes', variant: 'secondary'}
                ],
                fieldsMapping: {},
                parsedFileData: null,
                parsedFilePreviewData: null,
                results: {
                    columns: [],
                    rows: []
                },
                requestInProgress: false,
                rowLimit: 128
            }
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
                        this.errors = [`Your CSV file is being truncated to ${this.rowLimit} rows. This importer tool works synchronically against the BE (no Jobs/Queues) and a big load could end in timeouts or unresponsive FE.`]
                        this.$bvModal.show('errors-modal')
                    }

                    this.autoselectMatchingColumn()

                    this.$root.$nextStep()
                };

                reader.onerror = function () {
                    alert('Error reading CSV file.')
                };
            },
            autoselectMatchingColumn() {
                this.parsedFilePreviewData[0].forEach((v, k) => {
                    let columnName = v.trim()
                    if  (this.fields.map((i) => i.key).includes(columnName)) {
                        this.fieldsMapping[columnName] = k
                    }
                })
            },
            postData() {
                this.requestInProgress = true
                this.$axios.post('/api/contacts', {data: this.buildPostData()}).then((response) => {
                    this.results.rows = response.data
                    this.results.columns = _.uniq(_.flatten(_.map(response.data, (i) => _.keys(i))))
                    this.$root.$nextStep()
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
                        _.set(newRow, column, data);
                    })

                    return newRow;
                });
            }
        }
    }
</script>

<style lang="scss" scoped>
    .csv-input {
        overflow: hidden;
    }
</style>
