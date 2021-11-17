<template>
    <div>

        <div class="row">
            <div v-for="(section, i) in settings" v-bind:key="i" class="card mt-5 col-md-12">
                <div class="crad-header">
                    <h5 data-toggle="collapse" v-bind:data-target="'#settingsSection-'+i" role="button" aria-expanded="false" aria-controls="countriesSection" class="card-title" style="padding: 10px;">{{ section.sectionName }}</h5>
                </div>
                <div class="collapse multi-collapse show" v-bind:id="'settingsSection-'+i">
                    <div class="card-body">
                        <div class="card-columns">
                            <div v-for="setting in section.settings" v-bind:key="setting.id" class="card text-center">
                                <img v-if="setting.type == 'image'" v-bind:src=setting.value class="card-img-top" alt="">
                                <div class="card-body">
                                    <h5 class="card-title">{{ setting.name }}</h5>
                                    <p v-if="setting.type !== 'image'" class="card-text">{{ setting.value }}</p>
                                    <button class="btn btn-primary btn-block" v-on:click="setUpdateData(setting)" data-toggle="modal" data-target="#updateModal">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Modal -->
        <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update {{ updateData.name }} Value</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" v-model="updateData.name">
                        </div>
                        <div class="form-group">
                            <label for="value">Value</label>
                            <input v-if="updateData.type == 'string' || updateData.type == 'number'" v-bind:type=updateData.type class="form-control" id="value" v-model="updateData.value">
                            <input v-else-if="updateData.type == 'image'" type="file" class="form-control" id="value" v-on:change="handleFileUpload" ref="file">
                            <textarea v-else-if="updateData.type == 'textarea'" name="value" id="value" class="form-control" cols="30" rows="10" v-model="updateData.value"></textarea>
                            <select v-else-if="updateData.type = 'select'" name="value" id="value" class="form-control" v-model="updateData.value">
                                <option v-for="option in options" v-bind:key="option">{{option}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button v-on:click="updateSetting" type="button" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import VueSweetalert2 from 'vue-sweetalert2';
import bootstrap from 'bootstrap';
Vue.use(VueSweetalert2);

export default {
    async mounted() {
        let settings = await axios.get('/nova-vendor/settings');
        let sections = settings.data.map(setting => setting.section);
        sections = [...new Set(sections)];

        sections.forEach(section => {
            let sectionSettings = settings.data.filter(setting => setting.section === section);
            this.settings.push({sectionName: section, settings: sectionSettings});
        });
        // console.log(this.settings);
    },

    data (){
        return {
            settings: [],
            updateData: {},
            options: [],
            file: ''
        };
    },

    methods: {
        handleFileUpload(){
            this.file = this.$refs.file.files[0];
        },
        setUpdateData: function(setting){
            this.updateData = setting;
            if(setting.type == 'select'){
                this.options = setting.extra_data.options
            }
        },

        updateSetting: async function(){
            let url = `/nova-vendor/settings/${this.updateData.id}`;
            try{
                let setting = null;
                if(this.updateData.type == 'image'){
                    let formData = new FormData();
                    formData.append('id',       this.updateData.id);
                    formData.append('name',     this.updateData.name);
                    formData.append('value',    this.file);
                    formData.append('type',     this.updateData.type);
                    formData.append('_method',  'put');
                    setting = await axios.post(url, formData, {headers: { 'Content-Type': 'multipart/form-data' }});
                }else{
                    setting = await axios.put(url, this.updateData);
                }
                let section = this.settings.find(section => section.sectionName === setting.data.data.section);
                section.settings.find(setting => setting.id == this.updateData.id).value = setting.data.data.value;
                this.toastMessage('success', setting.data.msg);
            }catch(error){
                if(error.response.status == 400){
                    this.toastMessage('error', error.response.data.msg);
                }else{
                    this.toastMessage('error', error.message);
                }
            }
        },

        toastMessage: function(type, message){
            const Toast = Vue.swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: type,
                title: message
            })
        }
    }
}
</script>

<style>
@import "../../../node_modules/bootstrap/dist/css/bootstrap.min.css";
@import "../../../node_modules/bootstrap-vue/dist/bootstrap-vue.css";
@import "../../../node_modules/sweetalert2/dist/sweetalert2.min.css";
</style>
