<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    certificates: Array,
});
</script>

<template>
    <Head title="Certificates" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Certificates</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-end mb-4">
                            <Link :href="route('certificates.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Issue New Certificate
                            </Link>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Root CA</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Expires At</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="certificate in certificates" :key="certificate.id">
                                    <td class="px-6 py-4 whitespace-no-wrap">{{ certificate.name }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap">{{ certificate.domain }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap">{{ certificate.root_ca.name }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap">{{ certificate.expires_at }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        <Link :href="route('certificates.show', certificate.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">View</Link>
                                        <Link :href="route('certificates.edit', certificate.id)" class="text-green-600 hover:text-green-900 mr-2">Edit</Link>
                                        <button @click="deleteCertificate(certificate.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
