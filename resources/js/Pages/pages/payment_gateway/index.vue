<script>
import { Link, Head, useForm, router } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import Swal from "sweetalert2";
import { ref } from "vue";
import axios from "axios";
import { useI18n } from 'vue-i18n';

export default {
    data() {
        return {
            rightOffcanvas: false,
        };
    },
    components: {
        Layout,
        PageHeader,
        Head,
        Link,
    },
    props: {
        successMessage: String,
        alertMessage: String,
        app_for: String,
        settings: {
            type: Object,
            default: () => ({}),
        },
    },
    setup(props) {
        const { t } = useI18n();

        const form = useForm({
            enable_pawapay:        props.settings.enable_pawapay ?? false,
            pawapay_environment:   props.settings.pawapay_environment ?? 'sandbox',
            pawapay_sandbox_token: props.settings.pawapay_sandbox_token ?? '',
            pawapay_live_token:    props.settings.pawapay_live_token ?? '',

            enable_gfsolutions:     props.settings.enable_gfsolutions ?? false,
            gfsolutions_api_key:    props.settings.gfsolutions_api_key ?? '',
            gfsolutions_api_secret: props.settings.gfsolutions_api_secret ?? '',
            gfsolutions_base_url:   props.settings.gfsolutions_base_url ?? 'https://backend.gfinancials.com/api/v1',
        });

        const successMessage = ref(props.successMessage || '');
        const alertMessage = ref(props.alertMessage || '');

        const dismissMessage = () => {
            successMessage.value = "";
            alertMessage.value = "";
        };

        const handleCheckboxChange = (key) => {
            if (props.app_for == "demo") {
                form[key] = !form[key];
                Swal.fire(t('error'), t('you_are_not_authorised'), 'error');
            }
        };

        const handleSubmit = async () => {
            if (props.app_for == "demo") {
                Swal.fire(t('error'), t('you_are_not_authorised'), 'error');
                return;
            }
            try {
                let formData = new FormData();
                for (let key in form) {
                    if (key.startsWith('enable')) {
                        formData.append(key, form[key] ? 1 : 0);
                    } else {
                        formData.append(key, form[key]);
                    }
                }
                let response = await axios.post('/payment-gateway/update', formData);

                if (response.status === 201) {
                    successMessage.value = t('sms_configuration_updated_successfully');
                    router.get('/payment-gateway');
                } else {
                    alertMessage.value = t('failed_to_update_sms_configuration');
                }
            } catch (error) {
                console.error(error);
                alertMessage.value = t('failed_to_update_sms_configuration_catch');
            }
        };

        return {
            successMessage,
            alertMessage,
            dismissMessage,
            form,
            handleSubmit,
            handleCheckboxChange,
        };
    },
};
</script>

<template>
    <Layout>
        <Head title="Payment Gateway" />
        <PageHeader :title="$t('payment_gateway')" :pageTitle="$t('payment_gateway')" />
        <form @submit.prevent="handleSubmit">
        <BRow>
            <BCard v-if="app_for === 'demo'" no-body>
                <BCardHeader class="border-0">
                    <div class="alert bg-warning border-warning fs-18" role="alert">
                        <strong>{{ $t('note') }} : <em>{{ $t('actions_restricted_due_to_demo_mode') }}</em></strong>
                    </div>
                </BCardHeader>
            </BCard>

            <BCol lg="12">
                <BCard no-body>
                    <BCardBody class="border border-dashed border-end-0 border-start-0">
                        <BRow class="mt-4">

                            <!-- PawaPay -->
                            <BCol lg="6" class="mb-4">
                                <BCard no-body class="border h-100">
                                    <BCardHeader class="border-0">
                                        <div class="row border-bottom p-2">
                                            <div class="col-6">
                                                <h5 class="mt-1">PawaPay</h5>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch form-switch-lg float-end me-3">
                                                    <input
                                                        v-model="form.enable_pawapay"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="enable_pawapay"
                                                        @change="handleCheckboxChange('enable_pawapay')"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </BCardHeader>
                                    <BCardBody>
                                        <div class="text-center mb-4">
                                            <img src="https://pawapay.io/wp-content/uploads/2022/09/pawaPay_logo_dark.svg" style="height:40px;" alt="PawaPay" />
                                        </div>

                                        <!-- Environment -->
                                        <div class="mb-3">
                                            <label class="form-label">Environnement</label>
                                            <select class="form-select" v-model="form.pawapay_environment" :disabled="app_for === 'demo'">
                                                <option value="sandbox">Sandbox (test)</option>
                                                <option value="live">Live (production)</option>
                                            </select>
                                        </div>

                                        <!-- Sandbox Token -->
                                        <div class="mb-3" v-if="form.pawapay_environment === 'sandbox'">
                                            <label class="form-label">API Token Sandbox</label>
                                            <input
                                                :type="app_for === 'demo' ? 'password' : 'text'"
                                                :readonly="app_for === 'demo'"
                                                class="form-control"
                                                placeholder="Bearer token sandbox PawaPay"
                                                v-model="form.pawapay_sandbox_token"
                                            />
                                        </div>

                                        <!-- Live Token -->
                                        <div class="mb-3" v-if="form.pawapay_environment === 'live'">
                                            <label class="form-label">API Token Live</label>
                                            <input
                                                :type="app_for === 'demo' ? 'password' : 'text'"
                                                :readonly="app_for === 'demo'"
                                                class="form-control"
                                                placeholder="Bearer token live PawaPay"
                                                v-model="form.pawapay_live_token"
                                            />
                                        </div>

                                        <!-- Callback URLs -->
                                        <div class="mb-3">
                                            <label class="form-label">URL Callback Dépôt <small class="text-muted">(à configurer dans le dashboard PawaPay)</small></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm text-muted" readonly :value="settings.pawapay_deposit_callback" />
                                                <button class="btn btn-outline-secondary btn-sm" type="button" @click="() => navigator.clipboard.writeText(settings.pawapay_deposit_callback)">
                                                    <i class="ri-file-copy-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">URL Callback Retrait <small class="text-muted">(à configurer dans le dashboard PawaPay)</small></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm text-muted" readonly :value="settings.pawapay_payout_callback" />
                                                <button class="btn btn-outline-secondary btn-sm" type="button" @click="() => navigator.clipboard.writeText(settings.pawapay_payout_callback)">
                                                    <i class="ri-file-copy-line"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Save -->
                                        <div class="text-end mt-3">
                                            <button type="submit" class="btn btn-primary">{{ $t('save') }}</button>
                                        </div>
                                    </BCardBody>
                                </BCard>
                            </BCol>

                            <!-- GFSolutions -->
                            <BCol lg="6" class="mb-4">
                                <BCard no-body class="border h-100">
                                    <BCardHeader class="border-0">
                                        <div class="row border-bottom p-2">
                                            <div class="col-6">
                                                <h5 class="mt-1">GFSolutions</h5>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch form-switch-lg float-end me-3">
                                                    <input
                                                        v-model="form.enable_gfsolutions"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="enable_gfsolutions"
                                                        @change="handleCheckboxChange('enable_gfsolutions')"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </BCardHeader>
                                    <BCardBody>
                                        <div class="text-center mb-4">
                                            <span class="badge bg-primary-subtle text-primary fs-20 p-3 rounded-3">
                                                <i class="ri-bank-card-line fs-24 me-1"></i> GFSolutions
                                            </span>
                                        </div>

                                        <!-- API Key -->
                                        <div class="mb-3">
                                            <label class="form-label">API Key</label>
                                            <input
                                                :type="app_for === 'demo' ? 'password' : 'text'"
                                                :readonly="app_for === 'demo'"
                                                class="form-control"
                                                placeholder="gfs_xxxxxxxxxxxxxxxxxxxx"
                                                v-model="form.gfsolutions_api_key"
                                            />
                                        </div>

                                        <!-- API Secret -->
                                        <div class="mb-3">
                                            <label class="form-label">API Secret</label>
                                            <input
                                                type="password"
                                                :readonly="app_for === 'demo'"
                                                class="form-control"
                                                placeholder="Clé secrète GFSolutions"
                                                v-model="form.gfsolutions_api_secret"
                                            />
                                            <small class="text-muted">Visible une seule fois sur le dashboard GFSolutions</small>
                                        </div>

                                        <!-- Base URL -->
                                        <div class="mb-3">
                                            <label class="form-label">URL de base API</label>
                                            <input
                                                type="text"
                                                :readonly="app_for === 'demo'"
                                                class="form-control"
                                                placeholder="https://backend.gfinancials.com/api/v1"
                                                v-model="form.gfsolutions_base_url"
                                            />
                                        </div>

                                        <!-- Callback URL -->
                                        <div class="mb-3">
                                            <label class="form-label">URL Callback <small class="text-muted">(à configurer dans le dashboard GFSolutions)</small></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm text-muted" readonly :value="settings.gfsolutions_callback_url" />
                                                <button class="btn btn-outline-secondary btn-sm" type="button" @click="() => navigator.clipboard.writeText(settings.gfsolutions_callback_url)">
                                                    <i class="ri-file-copy-line"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Save -->
                                        <div class="text-end mt-3">
                                            <button type="submit" class="btn btn-primary">{{ $t('save') }}</button>
                                        </div>
                                    </BCardBody>
                                </BCard>
                            </BCol>

                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
        </form>

        <!-- Success Message -->
        <div v-if="successMessage" class="custom-alert alert alert-success alert-border-left fade show">
            <div class="alert-content">
                <i class="ri-notification-off-line me-3 align-middle"></i>
                <strong>Success</strong> - {{ successMessage }}
                <button type="button" class="btn-close btn-close-success" @click="dismissMessage" aria-label="Close"></button>
            </div>
        </div>

        <!-- Alert Message -->
        <div v-if="alertMessage" class="custom-alert alert alert-danger alert-border-left fade show">
            <div class="alert-content">
                <i class="ri-notification-off-line me-3 align-middle"></i>
                <strong>Alert</strong> - {{ alertMessage }}
                <button type="button" class="btn-close btn-close-danger" @click="dismissMessage" aria-label="Close"></button>
            </div>
        </div>
    </Layout>
</template>

<style>
.custom-alert {
    max-width: 600px;
    float: right;
    position: fixed;
    top: 90px;
    right: 20px;
}
</style>
