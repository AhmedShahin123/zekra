<template>
    <div>

         <div v-if="this.displayCourierList" class="card">
            <div class="card-body">
                <h5  class="card-title">Select Courier</h5>
                <form v-if="this.displayCourierList">
                    <div class="form-row mb-3">

                        <div class="col">
                            <select v-on:change="getCourierMatrices()" v-model="selectedCourrierId" class="form-control">
                                <option v-for="courier in couriers" v-bind:key="courier.id" v-bind:value="courier.id">{{ courier.user.name }}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <h3 v-else class="text-center">{{ this.user.name }} shipping prices</h3>

        <div class="row courier-matrices" v-if="this.selectedCourrierId !== null && this.selectedCourrierId !== ''">

            <div class="card mt-5 col-md-12">
                <div class="card-body">
                    <h5 class="card-title">City - Zone matrix</h5>

                    <form>
                        <div class="form-row mb-3">
                            <div class="col-xl-3 col-sm-12 mt-3">
                                <label for="zone_city">Zone Number</label>
                                <input type="number" v-model="newZone.zone" id="zone_city" class="form-control" placeholder="Zone Number">
                            </div>
                            <div class="col-xl-3 col-sm-12 mt-3">
                                <label for="Country">Country</label>
                                <select v-on:change="getCountryCities" v-model="newZone.country" id="country" class="form-control">
                                    <option value=""> -- Select Country -- </option>
                                    <option v-for="country in countries" v-bind:key="country.id" v-bind:value="country.id">{{ country.country_name }}</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-sm-12 mt-3">
                                <label for="city">City</label>
                                <select v-model="newZone.city" id="city" class="form-control">
                                    <option value=""> -- Select City -- </option>
                                    <option v-for="city in cities" v-bind:key="city.id" v-bind:value="city.id">{{ city.city_name }}</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-sm-12 mt-3">
                                <input v-on:click="createNewZone" type="button" class="btn btn-primary btn-block" style="margin-top: 31px;" value="Create">
                            </div>
                        </div>
                    </form>

                    <table class="table-hover table">
                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>Zone Number</th>
                                <th>Country</th>
                                <th>City</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(zone, index) in zones" v-bind:key="zone.id" class="text-center">
                                <td>{{ (index+1) }}</td>
                                <td>{{ zone.zone }}</td>
                                <td>{{ zone.city.country.country_name }}</td>
                                <td>{{ zone.city.city_name }}</td>
                                <td><button v-on:click="deleteZone(zone.id)" class="btn btn-danger">Delete</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-5 col-md-12">
                <div class="card-body">
                    <h5 class="card-title">Zone - Price matrix</h5>

                    <form>
                        <div class="form-row mb-3">
                            <div class="col-xl-6 col-sm-12 mt-3">
                                <label for="zone_price">Zone Number</label>
                                <input id="zone_price" v-model="newPrice.zone" type="number" class="form-control" placeholder="Zone Number">
                            </div>

                            <div class="col-xl-6 col-sm-12 mt-3">
                                <label for="primary_weight">Primary Weight (Grams)</label>
                                <input v-model="newPrice.primary_weight" id="primary_weight" type="number" class="form-control" placeholder="Primary Weight (Grams)">
                            </div>

                            <div class="col-xl-6 col-sm-12 mt-3">
                                <label for="primary_weight_price">Primary Weight Price</label>
                                <input v-model="newPrice.primary_weight_price" id="primary_weight_price" type="number" class="form-control" placeholder="Primary Weight Price">
                            </div>

                            <div class="col-xl-6 col-sm-12 mt-3">
                                <label for="additional_weight">Additional Weight (Grams)</label>
                                <input v-model="newPrice.additional_weight" id="additional_weight" type="number" class="form-control" placeholder="Additional Weight (Grams)">
                            </div>

                            <div class="col-xl-6 col-sm-12 mt-3">
                                <label for="additional_weight_price">Additional Weight Price</label>
                                <input v-model="newPrice.additional_weight_price" id="additional_weight_price" type="number" class="form-control" placeholder="Additional Weight Price">
                            </div>

                            <div class="col-xl-6 col-sm-12 mt-3">
                                <input v-on:click="createNewPrice" type="button" class="btn btn-primary btn-block" style="margin-top: 31px" value="Create">
                            </div>
                        </div>
                    </form>

                    <table class="table-hover table">
                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>Zone Number</th>
                                <th>Primary Weight</th>
                                <th>Primary Weight Price</th>
                                <th>Additional Weight</th>
                                <th>Additional Weight Price</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(price, index) in prices" v-bind:key="price.id" class="text-center">
                                <td>{{ (index+1) }}</td>
                                <td>{{ price.zone }}</td>
                                <td>{{ price.primary_weight }} Grams</td>
                                <td>{{ price.primary_weight_price }} $</td>
                                <td>{{ price.additional_weight }} Grams</td>
                                <td>{{ price.additional_weight_price }} $</td>
                                <td><button v-on:click="deletePrice(price.id)" class="btn btn-danger">Delete</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</template>

<script>
import Vue from 'vue';
import VueSweetalert2 from 'vue-sweetalert2';
Vue.use(VueSweetalert2);

export default {
    async created() {
        let user = await axios.get('/nova-vendor/ShippingMatrix/user');
        this.user = user.data;
        if(!this.user.is_admin){
            this.selectedCourrierId = this.user.courier.id;
            this.getCourierMatrices();
        }else{
            let couriers = await axios.get('/nova-vendor/ShippingMatrix/couriers');
            this.couriers = couriers.data;
            this.displayCourierList = true;
        }

        let countries = await axios.get('/nova-vendor/ShippingMatrix/countries');
        this.countries = countries.data;
    },
    data (){
        return {
            user: {name: null},
            couriers: [],
            courier: {},
            countries: [],
            cities: [],
            selectedCourrierId: null,
            zones: [],
            prices: [],
            newZone: {
                country: null,
                city: null,
                zone: null
            },
            newPrice: {
                zone: null,
                primary_weight: null,
                primary_weight_price: null,
                additional_weight: null,
                additional_weight_price: null,
            },
            displayCourierList: false
        }
    },
    methods: {
        getCountryCities: async function(){
            let cities = await axios.get(`/nova-vendor/ShippingMatrix/countries/${this.newZone.country}/cities`);
            this.cities = cities.data;
        },

        getCourierMatrices: async function () {
            let courier = await axios.get(`/nova-vendor/ShippingMatrix/couriers/${this.selectedCourrierId}`);

            this.courier = courier.data;
            this.zones = this.courier.zones;
            this.prices = this.courier.prices;
        },

        createNewZone: async function(){
            let url = `/nova-vendor/ShippingMatrix/couriers/${this.selectedCourrierId}/zones`;
            try{
                let zone = await axios.post(url, this.newZone);
                this.getCourierMatrices();
                this.toastMessage('success', zone.data.msg);
                this.newZone = {
                    city: null,
                    zone: null
                }
            }catch(error){
                if(error.response.status == 400){
                    this.toastMessage('error', error.response.data.msg);
                }else{
                    this.toastMessage('error', error.message);
                }
            }
        },

        createNewPrice: async function(){
            let url = `/nova-vendor/ShippingMatrix/couriers/${this.selectedCourrierId}/prices`;
            try{
                let price = await axios.post(url, this.newPrice);
                this.getCourierMatrices();
                this.toastMessage('success', price.data.msg);
                this.newPrice = {
                    zone: null,
                    primary_weight: null,
                    primary_weight_price: null,
                    additional_weight: null,
                    additional_weight_price: null,
                }
            }catch(error){
                if(error.response.status == 400){
                    this.toastMessage('error', error.response.data.msg);
                }else{
                    this.toastMessage('error', error.message);
                }
            }
        },

        deleteZone: function(zoneId){
            Vue.swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then( async (result) => {
                if (result.value) {
                    let url = `/nova-vendor/ShippingMatrix/couriers/${this.selectedCourrierId}/zones/${zoneId}`
                    try{
                        let deleted = await axios.delete(url);
                        this.getCourierMatrices();
                    }catch(error){
                        Vue.swal(
                            'Deleted!',
                            error.message,
                            'error'
                        );
                    }

                }
            })
        },

        deletePrice: function(priceId){

            Vue.swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then( async (result) => {
                if (result.value) {
                    let url = `/nova-vendor/ShippingMatrix/couriers/${this.selectedCourrierId}/prices/${priceId}`
                    try{
                        let deleted = await axios.delete(url);
                        Vue.swal(
                            'Deleted!',
                            deleted.data.msg,
                            'success'
                        );
                        this.getCourierMatrices();
                    }catch(error){
                        Vue.swal(
                            'Deleted!',
                            error.message,
                            'error'
                        );
                    }

                }
            })
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
/* Scoped Styles */
@import "../../../node_modules/bootstrap/dist/css/bootstrap.min.css";
@import "../../../node_modules/bootstrap-vue/dist/bootstrap-vue.css";
@import "../../../node_modules/sweetalert2/dist/sweetalert2.min.css";
</style>
