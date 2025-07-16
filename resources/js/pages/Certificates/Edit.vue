<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';

const props = defineProps({
    certificate: Object,
    rootCas: Array
});

const form = useForm({
    root_ca_id: props.certificate.root_ca_id,
    name: props.certificate.name,
    domain: props.certificate.domain,
    passphrase: props.certificate.passphrase,
    expires_at: props.certificate.expires_at,
    valid_from: props.certificate.valid_from
});

const submit = () => {
    form.put(route('certificates.update', props.certificate.id));
};
</script>

<template>
    <Head :title="`Edit ${certificate.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit {{ certificate.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="root_ca_id" class="block text-sm font-medium text-gray-700">Root CA</label>
                                <select id="root_ca_id" v-model="form.root_ca_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a Root CA</option>
                                    <option v-for="rootCa in rootCas" :key="rootCa.id" :value="rootCa.id">{{ rootCa.name
                                        }}
                                    </option>
                                </select>
                                <div v-if="form.errors.root_ca_id" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.root_ca_id }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <Input type="text" id="name" v-model="form.name"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="domain" class="block text-sm font-medium text-gray-700">Domain</label>
                                <Input type="text" id="domain" v-model="form.domain"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="form.errors.domain" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.domain }}
                                </div>
                            </div>


                            <div class="mb-4">
                                <label for="passphrase"
                                       class="block text-sm font-medium text-gray-700">Passphrase</label>
                                <Input type="password" id="passphrase" v-model="form.passphrase"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="form.errors.passphrase" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.passphrase }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="expires_at" class="block text-sm font-medium text-gray-700">
                                    Expires At
                                </label>
                                <Input type="date" id="expires_at" v-model="form.expires_at"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="form.errors.expires_at" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.expires_at }}
                                </div>
                            </div>

                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Certificate
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
