<template>
    <div class="flex items-center">
        <div class="flex w-full bg-white rounded">
            <dropdown class="px-4 md:px-6 rounded-l border-r" placement="bottom-start" :width="5" align="left">
                <template v-slot:trigger>
                    <label class="block text-gray-700">Match</label>
                    <label class="block text-gray-700 font-bold">{{ match }}</label>
                </template>
                <template v-slot:content>
                    <div class="my-2 px-4 bg-white rounded">
                        <slot></slot>
                    </div>
                </template>
            </dropdown>
            <input type="text" class="relative w-full px-6 py-3 rounded-r focus:ring" autocomplete="off" name="search" placeholder="Search..." @input="$emit('search-term', $event.target.value)" />
        </div>
    </div>
</template>

<script>
import Dropdown from '@/Jetstream/Dropdown'
export default {
    components:{
        Dropdown,
    },
    props:{
        match: {
            type: String,
            required: true,
        },
        maxWidth: {
            type: Number,
            default: 300,
        },
    },
    emits: ['search-term']
}
</script>