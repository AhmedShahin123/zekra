<template>
    <card class="flex flex-col items-center justify-center">
        <div class="px-3 py-3">
            <h1 class="text-center text-3xl text-80 font-light">Transactions Total: {{ this.loading ? 'calculating ...' : this.totalAmount }}</h1>
        </div>
    </card>
</template>

<script>
export default {
    props: [
        'card'
    ],

    data(){
        return {
            totalAmount: 0,
            loading: true
        }
    },

   watch:{
        async $route (to, from) {
            try{
                let queryString = this.formatQueryString(to.query);
                this.totalAmount = await this.getTotalTransactions(queryString);
            }catch(error){
                this.loading = false;
                console.log({error});
            }
        }
    },

    async mounted() {
        try{
            let queryString = this.formatQueryString(this.$route.query);
            this.totalAmount = await this.getTotalTransactions(queryString);
        }catch(error){
            this.loading = false;
            console.log({error});
        }
    },

    methods:{
        formatQueryString: function(query){

            let queryString = "";

            if(query['_search']){
                queryString += `search=${query['_search']}`;
            }

            if(query[`${this.card.type}_filter`]){
                queryString += `&filters=${query[`${this.card.type}_filter`]}`;
            }

            if(query[`${this.card.type}_per_page`]){
                queryString += `&perPage=${query[`${this.card.type}_per_page`]}`;
            }

            if(query[`${this.card.type}_page`]){
                queryString += `&page=${query[`${this.card.type}_page`]}`;
            }

            if(query[`${this.card.type}_order`]){
                queryString += `&orderBy=${query[`${this.card.type}_order`]}`;
            }

            if(query[`${this.card.type}_direction`]){
                queryString += `&orderByDirection=${query[`${this.card.type}_direction`]}`;
            }
            
            return queryString;
        },

        getTotalTransactions: async function(queryString){
            this.loading = true;
            let request = await axios.get(`/nova-api/${this.card.type}?${queryString}`);
            let transactions = request.data;
            let total = 0;
            transactions.resources.forEach(resource => {
                let amountField = resource.fields.find(field => field.name == 'amount');
                let amount = amountField.value;
                amount = parseFloat(amount);
                total += amount;
            });
            this.loading = false;

            return total;
        }
    }
}
</script>

