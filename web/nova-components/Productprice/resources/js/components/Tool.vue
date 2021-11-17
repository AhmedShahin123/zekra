<template>
    <div>

         <div v-if="this.displayProductList" class="card">
            <div class="card-body">
                <h5  class="card-title">Select Product</h5>
                <form v-if="this.displayProductList">
                    <div class="form-row mb-3">

                        <div class="col">
                            <select v-on:change="getProductMatrices()" v-model="selectedProductId" class="form-control">
                                <option v-for="product in products" v-bind:key="product.id" v-bind:value="product.id">{{ product.dimensions }}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <h3 v-else class="text-center"> Product Price</h3>

        <div class="row product-matrices" v-if="this.selectedProductId !== null && this.selectedProductId !== ''">

            <div class="card mt-5 col-md-12">
                <div class="card-body">
                    <h5 class="card-title">Country - City Price</h5>

                    <form>
                        <div class="form-row mb-3">
                            <div class="col-xl-3 col-sm-12 mt-3">
                                <label for="price">Price</label>
                                <input type="number" v-model="newPrice.price" id="price" class="form-control" placeholder="Product Price">
                            </div>
                            <div class="col-xl-3 col-sm-12 mt-3">
                                <label for="country">Country</label>
                                <select v-on:change="getCountryCities" v-model="newPrice.country" id="country" class="form-control">
                                    <option value=""> -- Select Country -- </option>
                                    <option v-for="country in countries" v-bind:key="country.id" v-bind:value="country.id">{{ country.country_name }}</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-sm-12 mt-3">
                                <label for="city">City</label>
                                <select v-model="newPrice.city" id="city" class="form-control">
                                    <option value=""> -- Select City -- </option>
                                    <option v-for="city in cities" v-bind:key="city.id" v-bind:value="city.id">{{ city.city_name }}</option>
                                </select>
                            </div>
                            

                            <div class="col-xl-3 col-sm-12 mt-3">
                                <input v-on:click="createNewPrice" type="button" class="btn btn-primary btn-block" style="margin-top: 31px;" value="Create">
                            </div>
                        </div>
                    </form>

                    <table class="table-hover table">
                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>Price</th>
                                <th>Country</th>
                                <th>City</th>
                                <th>Delete</th>


                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(price, index) in prices" v-bind:key="price.id" class="text-center">
                                <td>{{ (index+1) }}</td>
                                <td>{{ price.price }}</td>
                                <td>{{ price.city.country.country_name }}</td>
                                <td>{{ price.city.city_name }}</td>
                                <td><button v-on:click="deleteProductPrice(price.id)" class="btn btn-danger">Delete</button></td>

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

            let products = await axios.get('/nova-vendor/productprice/products');
            this.products = products.data;
            this.displayProductList = true;


        let countries = await axios.get('/nova-vendor/productprice/countries');
        this.countries = countries.data;
    },
    data (){
        return {
            user: {name: null},
            products: [],
            product: {},
            countries: [],
            cities: [],
            prices: [],
            selectedProductId: null,
            newPrice: {
                country: null,
                city: null,
                price: null,
                product : null
            },
            displayProductList: true
        }
    },
    methods: {
        getCountryCities: async function(){
            let cities = await axios.get(`/nova-vendor/productprice/countries/${this.newPrice.country}/cities`);
            this.cities = cities.data;
        },

        getProductMatrices: async function () {
            let prices = await axios.get(`/nova-vendor/productprice/products/${this.selectedProductId}`);
            this.prices = prices.data;
            console.log(this.prices);
        },

        createNewPrice: async function(){
            let url = `/nova-vendor/productprice/createproductprice/${this.selectedProductId}`;
            try{
                let price = await axios.post(url,this.newPrice);
                this.getProductMatrices();
                this.toastMessage('success', price.data.msg);
                this.newPrice = {
                    city: null,
                    price: null
                }
            }catch(error){
                if(error.response.status == 400){
                    this.toastMessage('error', error.response.data.msg);
                }else{
                    this.toastMessage('error', error.message);
                }
            }
        },


        deleteProductPrice: function(priceId){
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
                    let url = `/nova-vendor/productprice/deleteproductpirce/${priceId}`
                    try{
                        let deleted = await axios.delete(url);
                        this.getProductMatrices();
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
@import "../../../node_modules/sweetalert2/dist/sweetalert2.min.css";
@import "../../../node_modules/bootstrap/dist/css/bootstrap.min.css";
@import "../../../node_modules/bootstrap-vue/dist/bootstrap-vue.css";
</style>
