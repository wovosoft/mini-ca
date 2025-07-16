<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    rootCa: Record<string, any>
}>();
</script>

<template>
    <Head :title="rootCa.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ rootCa.name }}
            </h2>
        </template>

        <div class="p-6 text-gray-900">
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700">Name:</p>
                <p class="mt-1 text-lg font-semibold">
                    {{ rootCa.name }}
                </p>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700">Domain:</p>
                <p class="mt-1 text-lg font-semibold">
                    {{ rootCa.domain }}
                </p>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700">Description:</p>
                <p class="mt-1 text-lg font-semibold">{{ rootCa.description }}</p>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700">Private Key:</p>
                <pre class="mt-1 p-2 bg-gray-100 rounded-md text-sm overflow-auto" v-html="rootCa.private_key"></pre>
                <Button as="a" :href="route('root-cas.download', {root_ca:rootCa.id,type:'private-key'})">
                    Download Private Key
                </Button>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700">Public Key:</p>
                <pre class="mt-1 p-2 bg-gray-100 rounded-md text-sm overflow-auto" v-html="rootCa.public_key"></pre>
                <Button as="a" :href="route('root-cas.download', {root_ca:rootCa.id,type:'public-key'})">
                    Download Public Key
                </Button>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700">Certificate:</p>
                <pre class="mt-1 p-2 bg-gray-100 rounded-md text-sm overflow-auto" v-html="rootCa.certificate"></pre>
                <Button as="a" :href="route('root-cas.download', {root_ca:rootCa.id,type:'certificate'})">
                    Download Certificate
                </Button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
