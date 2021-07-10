<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Students</h2>
        </template>

        <div class="bg-white shadow sm:rounded-lg p-3 m-4">
            <search-filter @search-term="setTerm" :match="match[form.match]" class="w-full max-w-md mr-4">
                <div class="my-1 cursor-pointer hover:bg-gray-200" @click="setMatch('exact')" value="exact">{{ this.match['exact'] }}</div>
                <div class="my-1 cursor-pointer hover:bg-gray-200" @click="setMatch('accurate')" value="accurate">{{ this.match['accurate'] }}</div>
                <div class="my-1 cursor-pointer hover:bg-gray-200" @click="setMatch('approximate')" value="approximate">{{ this.match['approximate'] }}</div>
            </search-filter>
        </div>
        <div class="bg-white rounded-md shadow overflow-x-auto m-4 p-2">
            <table class="w-full whitespace-nowrap">
                <tr class="text-left font-bold">
                    <th class="px-3 pt-3 pb-3">Name</th>
                    <th class="px-3 pt-3 pb-3">Email</th>
                </tr>
                <tr v-for="student in students.data" :key="student.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t p-2">{{ student.name }}</td>
                    <td class="border-t p-2">{{ student.email }}</td>
                </tr>
            </table>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
    import SearchFilter from './SearchFilter.vue'
    import throttle from 'lodash/throttle'
    import pickBy from 'lodash/pickBy'
    import mapValues from 'lodash/mapValues'

    export default {
        components:{
            AppLayout,
            SearchFilter,
        },
        props:{
            filters: Object,
            students: Object,
        },
        data(){
            return {
                form: {
                    search: this.filters.search,
                    match: 'exact',
                },
                match: {
                    exact: 'مماثل',
                    accurate: 'دقيق',
                    approximate: 'تقريبي',
                }
            }
        },
        watch: {
            form: {
                deep: true,
                handler: throttle(function() {
                    this.$inertia.get(this.route('students'), pickBy(this.form), { preserveState: true })
                }, 1000),
            }
        },
        methods: {
            setMatch(match) {
                this.form.match = match
            },
            setTerm(term) {
                this.form.search = term
            }
        },
    }
</script>