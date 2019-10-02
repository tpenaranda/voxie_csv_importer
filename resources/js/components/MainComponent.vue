<template>
    <div class="main">
        <img src="https://www.voxie.com/wp-content/uploads/elementor/thumbs/VOXIE-black-o1ol2ora2qld6vodwmsrb0qd5dj7ere5midef05xk8.png" width="400">
        <h2>CSV File Parser</h2>
        <vue-csv-import ref="importer"
            v-model="csvData"
            headers=true
            url="/hello"
            tableClass="table-striped"
            inputClass="input-group"
            :map-fields="csvFields">

            <template slot="hasHeaders" slot-scope="{headers, toggle}">
                <label>
                    <input type="checkbox" id="hasHeaders" :value="headers" @change="toggle">
                    CSV file has header?
                </label>
            </template>

            <template slot="thead">
                <tr>
                    <th>Field</th>
                    <th>Get from Column</th>
                </tr>
            </template>

            <template slot="next" slot-scope="{load}">
                <button @click.prevent="load" class="btn btn-success">Load data</button>
            </template>

            <template slot="submit">
                <button @click.prevent="postData">Submit</button>
            </template>
        </vue-csv-import>
    </div>
</template>

<script>
    import { VueCsvImport } from 'vue-csv-import';

    export default {
        name: 'MainComponent',
        components: {
            VueCsvImport
        },
        data: () => ({
            csvData: [],
            csvFields: {
                email: 'Email [string, unique]',
                fb_messenger_id: 'Facebook Messenger #ID [string]',
                first_name: 'First Name [string]',
                last_name: 'Last Name [string]',
                phone: 'Phone [required, string]',
                sticky_phone_number_id: 'Sticky Phone Number #ID [integer]',
                team_id: '<span>Team #ID [required, integer]</span>',
                time_zone: 'Time Zone [string]',
                twitter_id: 'Twitter #ID [string]',
                unsubscribed_status: 'Unsubscribed Status [required, string]'
            },
            requestInProgress: false
        }),
        computed: {
        },
        mounted () {
        },
        methods: {
            postData() {
                this.requestInProgress = true
                axios.post('/api/contacts', {data: this.getImporterData()}).then((response) => {
                    alert('POST Done!')
                }).catch((error) => {
                    alert('POST Error')
                }).finally((response) => {
                    this.requestInProgress = false
                })
            },
            getImporterData() {
                let importer = this.$refs.importer
                let parsedFile = _.clone(importer.csv)
                let mappedColumns = Object.values(importer.map)

                if (importer.hasHeaders) {
                    parsedFile.shift()
                }

                return _.map(parsedFile, (row) => {
                    let newRow = {};

                    _.forEach(importer.map, (column, fieldName) => {
                        _.set(newRow, fieldName, _.get(row, column));
                    })

                    _.forEach(row.filter((data, column) => !mappedColumns.includes(column)), (data, column) => {
                        _.set(newRow, `Not mapped column #${column + 1}`, _.get(row, column));
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
    .csv-import-file {
        margin: 5em;
    }
    div.main {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      min-height: 100vh;
      border: 1px solid;
    }
</style>
