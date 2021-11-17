<template>
    <div>

        <div class="card">
            <div class="card-body">
                <h5  class="card-title">Select Language</h5>
                <form>
                    <div class="form-row mb-3">
                        <div class="col">
                            <select v-model="selectedLanguage" class="form-control">
                                <option v-for="language in languages" v-bind:key="language.id" v-bind:value="language.locale">{{ language.name }}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row courier-matrices" v-if="selectedLanguage !== null && selectedLanguage !== ''">

            <div class="card mt-5 col-md-12">
                <div class="crad-header">
                    <h5 data-toggle="collapse" data-target="#countriesSection" role="button" aria-expanded="false" aria-controls="countriesSection" class="card-title" style="padding: 10px;">Countries</h5>
                </div>
                <div class="collapse multi-collapse show" id="countriesSection">
                    <div class="card-body">

                        <table class="table-hover table">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Country name</th>
                                    <th>Save</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(country, index) in countries" v-bind:key="country.id" class="text-center">
                                    <td>{{ (index+1) }}</td>
                                    <td>{{ country.country_name }}</td>
                                    <td><input v-model="translations.countries[selectedLanguage][country.id]" class="form-control text-center" type="text"></td>
                                    <td><button v-on:click="translateCountry(country.id)" class="btn btn-success">Save</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-5 col-md-12">
                <div class="crad-header">
                    <h5 data-toggle="collapse" data-target="#citiesSection" role="button" aria-expanded="false" aria-controls="citiesSection" class="card-title" style="padding: 10px;">Cities</h5>
                </div>
                <div class="collapse multi-collapse show" id="citiesSection">
                    <div class="card-body">

                        <table class="table-hover table">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>City name</th>
                                    <th>Save</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(city, index) in cities" v-bind:key="city.id" class="text-center">
                                    <td>{{ (index+1) }}</td>
                                    <td>{{ city.city_name }}</td>
                                    <td><input v-model="translations.cities[selectedLanguage][city.id]" class="form-control text-center" type="text"></td>
                                    <td><button v-on:click="translateCity(city.id)" class="btn btn-success">Save</button></td>
                                </tr>
                            </tbody>
                        </table>
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
        let languages = await axios.get('/nova-vendor/translations/languages');
        this.languages = languages.data;
        
        let countries = await axios.get('/nova-vendor/translations/countries');
        this.countries = countries.data;

        let cities = await axios.get('/nova-vendor/translations/cities');
        this.cities = cities.data;

        this.languages.forEach(({locale}) => {
            this.translations.countries[locale] = [];
            this.translations.cities[locale] = [];

            this.countries.forEach(country => {
                let countryTranslation = country.translations.find(translation => translation.locale == locale);
                let translationValue = countryTranslation ? countryTranslation.country_name : '';
                this.translations.countries[locale][country.id] = translationValue;
            });

            this.cities.forEach(city => {
                let cityTranslation = city.translations.find(translation => translation.locale == locale);
                let translationValue = cityTranslation ? cityTranslation.city_name : '';
                this.translations.cities[locale][city.id] = translationValue;
            });
        });

    },
    data (){
        return {
            languages: [],
            selectedLanguage: null,
            countries: [],
            cities: [],
            translations: {
                countries: [],
                cities: []
            }
        }
    },
    methods: {
        translateCountry: async function(countryId){
            let url = `/nova-vendor/translations/countries/${countryId}/translations`;
            let data = {
                locale: this.selectedLanguage,
                country_name: this.translations.countries[this.selectedLanguage][countryId]
            };

            try{
                let translation = await axios.put(url, data);
                this.toastMessage('success', translation.data.msg);
            }catch(error){
                if(error.response.status == 400){
                    this.toastMessage('error', error.response.data.msg);
                }else{
                    this.toastMessage('error', error.message);
                }
            }
            
        },

        translateCity: async function(cityId){
            let url = `/nova-vendor/translations/cities/${cityId}/translations`;
            let data = {
                locale: this.selectedLanguage,
                city_name: this.translations.cities[this.selectedLanguage][cityId]
            };

            try{
                let translation = await axios.put(url, data);
                this.toastMessage('success', translation.data.msg);
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
/* Scoped Styles */
@import "../../../node_modules/bootstrap/dist/css/bootstrap.min.css";
@import "../../../node_modules/bootstrap-vue/dist/bootstrap-vue.css";
@import "../../../node_modules/sweetalert2/dist/sweetalert2.min.css";
</style>
