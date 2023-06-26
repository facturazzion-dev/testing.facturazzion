<template>
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" @submit.prevent="uploadFile">
                <a :href="downloadurl" class="btn btn-info">Descargar Plantilla</a>
                <div class="fileinput fileinput-new" data-provides="fileinput">
                <span class="btn btn-default btn-file"><span class="fileinput-new">Seleccionar Archivo</span><span class="fileinput-exists">Cambiar Archivo</span>
                <input type="file" name="..." ref="fileInput">
                </span>
                    <span class="fileinput-filename"></span>
                    <a href="#" class="close fileinput-exists import-cat" data-dismiss="fileinput">&times;</a>
                </div>
                <br>
                <button class="btn btn-primary">Cargar y Revisar</button>
                
            </form>
            <h5 v-if="total" class="m-t-20">Importados : {{ completed.length }} / {{ total }}</h5>
            <div class="table-responsive">
                <table class="table sales-team import-wrapper table-bordered" v-if="total">
                    <thead>
                    <tr>
                        <th>
                            <label class="md-check">
                                <input type="checkbox" v-model="selectedAll">
                                <i class="primary"></i>
                            </label>
                        </th>
                        <th>Nombre Comercial</th>
                        <th>Correo electrónico</th>
                        <th>Telefono</th>
                        <th>Razon Social</th>
                        <th>RFC</th>
                        <th>Calle</th>
                        <th>No. Exterior</th>
                        <th>No. Interior</th>
                        <th>Colonia</th>
                        <th>Código Postal</th>
                        <th>País</th>                        
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in data" :class="{'alert-info':item.created}">
                        <td>
                            <label class="md-check" v-if="!item.created">
                                <input type="checkbox" v-model="item.selected">
                                <i class="primary"></i>
                            </label>
                        </td>
                        
                        <td> {{ item.name }} </td>
                        
                        <td> {{ item.email }} </td>
                        
                        <td> {{ item.phone }} </td>
                        
                        <td> {{ item.sat_name }} </td>
                        
                        <td> {{ item.sat_rfc }} </td>
                        
                        <td> {{ item.street }} </td>
                        
                        <td> {{ item.exterior_no }} </td>
                        
                        <td> {{ item.interior_no }} </td>
                        
                        <td> {{ item.suburb }} </td>
                        
                        <td> {{ item.zip_code }} </td>
                        
                        <td>
                            <select class="form-control" name="country_id" v-model="item.country_id">
                                <option v-for="country in countries" :value="country.id">{{country.text}}</option>
                            </select>
                        </td>
                        
                        <td>
                            <button v-if="!item.created" class="btn btn-primary btn-sm" @click="createRecord(item)">Crear</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-md-12 m-t-10">
                    <a v-show="remaining.length > 0" :class="{ 'disabled': !selected.length }" href="" @click.prevent="createAll" class="btn btn-primary pull-right">Crear Seleccionados</a>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['url'],

        data: function() {
            return {
                data: [],
                staff: null,
                countries: [],
                selectedAll: false
            }
        },

        filters: {
            success: function(items) {
                return items.filter(function(item) {
                    return item.created;
                });
            },

            rejected: function(items) {
                return items.filter(function(item) {
                    return item.errors
                });
            }
        },

        computed: {
            completed: function() {
                return this.data.filter(function(item) {
                    return item.created;
                });
            },

            remaining: function() {
                return this.data.filter(function(item) {
                    return !item.created;
                });
            },

            total: function() {
                return this.data.length;
            },

            selected: function() {
                return this.data.filter(function(item) {
                    return item.selected;
                });
            },
            downloadurl: function() {
                return this.url + "download-template";
            }
        },

        methods: {
            init: function(res) {
                //Excel ROWS
                this.data = res.data.map((item)=> {
                    item.created = false;
                    item.errors = false;
                    item.selected = false;
                    return item;
                });

                //county data to be used
                this.countries = res.countries;

                //Look for select all checkbox
                this.$watch('selectedAll', function(selected) {
                    this.updateRowsSelection(selected);
                });

                this.selectedAll = false;
            },

            updateRowsSelection: function(status) {
                this.data.forEach((item)=> {
                    item.selected = status;
                });
            },

            uploadFile: function() {

                var formData = new FormData();
                formData.append('file', this.$refs.fileInput.files[0]);

                axios.post(this.url + 'import', formData)
                    .then(res => {
                        this.init(res.data);
                    }).catch(err => {
                    alert(err.response.data);
                });
            },

            createRecord: function(item) {
                if (!item.created) {
                    var vm = this;
                    axios.post(this.url + 'ajax-store', item)
                        .then(function(response) {
                            item.created = true;
                            item.selected = false;
                            item.errors = null;
                        })
                        .catch(function(error) {
                            console.log(error);
                            item.errors = error;
                        });
                }
            },

            createAll: function() {
                this.selected.forEach(function(item) {
                    this.createRecord(item);
                }.bind(this));
            }
        }
    }
</script>
