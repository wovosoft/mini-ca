<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import dayjs from 'dayjs';

defineProps<{
    certificate: Record<string, any>
}>();
</script>

<template>
    <Head :title="certificate.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ certificate.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700">Name:</p>
                            <p class="mt-1 text-lg font-semibold">{{ certificate.name }}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700">Domain:</p>
                            <p class="mt-1 text-lg font-semibold">{{ certificate.domain }}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700">Root CA:</p>
                            <p class="mt-1 text-lg font-semibold">{{ certificate.root_ca.name }}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700">Certificate:</p>
                            <pre class="mt-1 p-2 bg-gray-100 rounded-md text-sm overflow-auto"
                                 v-html="certificate.certificate"></pre>
                            <Button class="mt-3" as="a" :href="route('certificates.download', certificate.id)">
                                Download Certificate
                            </Button>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700">Private Key:</p>
                            <pre class="mt-1 p-2 bg-gray-100 rounded-md text-sm overflow-auto"
                                 v-html="certificate.private_key"></pre>
                            <Button class="mt-3" as="a"
                                    :href="route('certificates.download-private-key', certificate.id)">
                                Download Private Key
                            </Button>
                        </div>


                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700">Expires At:</p>
                            <p class="mt-1 text-lg font-semibold">
                                {{ dayjs(certificate?.expires_at).format('DD/MM/YYYY') }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700">Valid From:</p>
                            <p class="mt-1 text-lg font-semibold">
                                {{ dayjs(certificate?.valid_from).format('DD/MM/YYYY') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
