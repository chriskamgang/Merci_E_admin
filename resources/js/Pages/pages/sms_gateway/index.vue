<script>
import { Link, Head, useForm, router, usePage } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import Pagination from "@/Components/Pagination.vue";
import Swal from "sweetalert2";
import { ref, watch } from "vue";
import axios from "axios";
import "@vueform/multiselect/themes/default.css";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import search from "@/Components/widgets/search.vue";
import searchbar from "@/Components/widgets/searchbar.vue";
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
        Pagination,
        flatPickr,
        Link,
        search,
        searchbar,
    },
    props: {
        successMessage: String,
        alertMessage: String,
        app_for: String,
        settings: Object,
    },
    setup(props) {
        const { t } = useI18n();
        const form = useForm({
            enable_nexah: props.settings?.enable_nexah ?? false,
            nexah_user: props.settings?.nexah_user ?? '',
            nexah_password: props.settings?.nexah_password ?? '',
            nexah_sender_id: props.settings?.nexah_sender_id ?? '',
        });

        const successMessage = ref(props.successMessage || '');
        const alertMessage = ref(props.alertMessage || '');

        const dismissMessage = () => {
            successMessage.value = "";
            alertMessage.value = "";
        };

        const handleCheckboxChange = (key) => {
            if(props.app_for == "demo"){
                form[key] = !form[key];
                Swal.fire(t('error'), t('you_are_not_authorised'), 'error');
                return;
            }
            Object.keys(form).forEach((formKey) => {
                if (formKey.startsWith('enable_')) {
                    form[formKey] = (formKey === key);
                }
            });
            handleSubmit();
        };

        const handleSubmit = async () => {
            if(props.app_for == "demo"){
                Swal.fire(t('error'), t('you_are_not_authorised'), 'error');
                return;
            }
            try {
                let formData = new FormData();
                for (let key in form) {
                    if(key.startsWith('enable')){
                        formData.append(key, form[key] ? 1 : 0);
                    }else{
                        formData.append(key, form[key] ?? '');
                    }
                }

                let response = await axios.post('/sms-gateway/update', formData);
                console.log("formdata",form.data());

                if (response.status === 201) {
                    successMessage.value = t('sms_configuration_updated_successfully');
                    setTimeout(() => {
                        router.get('/sms-gateway');
                        form.reset();
                    }, 5000);
                } else {
                    alertMessage.value = t('failed_to_update_sms_configuration');
                }
            } catch (error) {
                console.error(t('error_updating_sms_configuration'), error);
                alertMessage.value = t('failed_to_update_sms_configuration_catch');
            }
        };

        watch(() => props.settings, (newSettings) => {
            Object.assign(form, newSettings);
        }, { immediate: true });

        return {
            form,
            successMessage,
            alertMessage,
            dismissMessage,
            handleCheckboxChange,
            handleSubmit,
        };
    },
};
</script>

<template>
    <Layout>
        <Head title="SMS Gateway" />
        <PageHeader :title="$t('sms_gateway')" :pageTitle="$t('sms_gateway')" />
        <form @submit.prevent="handleSubmit">
            <BRow>
                <BCard v-if="app_for === 'demo'" no-body id="tasksList">
                    <BCardHeader class="border-0">
                        <div class="alert bg-warning border-warning fs-18" role="alert">
                            <strong> {{$t('note')}} : <em> {{$t('actions_restricted_due_to_demo_mode')}}</em> </strong>
                        </div>
                    </BCardHeader>
                </BCard>
                <BCol lg="12">
                    <BCard no-body id="tasksList">
                        <BCardHeader class="border-0"></BCardHeader>
                        <BCardBody class="border border-dashed border-end-0 border-start-0">
                            <BRow class="mt-4">
                                <BCol lg="6">
                                    <BCard no-body id="tasksList" class="border">
                                        <BCardHeader class="border-0 mt-2 p-4 border-bottom">
                                            <div class="row">
                                                <div class="col-6">
                                                    <h5>Nexah SMS</h5>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-check form-switch form-switch-lg float-end me-3">
                                                        <input v-model="form.enable_nexah" class="form-check-input" type="checkbox" role="switch" id="enable_nexah" @change="handleCheckboxChange('enable_nexah')" />
                                                    </div>
                                                </div>
                                            </div>
                                        </BCardHeader>
                                        <BCardBody>
                                            <div class="text-center mb-4">
                                                <span class="fw-bold fs-4 text-primary">nexah</span>
                                            </div>
                                            <div class="mb-3">
                                                <label for="nexah_user" class="form-label">Utilisateur</label>
                                                <input :readonly="app_for === 'demo'" v-model="form.nexah_user" :type="app_for === 'demo' ? 'password' : 'text'" class="form-control" placeholder="Votre identifiant Nexah" id="nexah_user" />
                                            </div>
                                            <div class="mb-3">
                                                <label for="nexah_password" class="form-label">Mot de passe</label>
                                                <input :readonly="app_for === 'demo'" v-model="form.nexah_password" type="password" class="form-control" placeholder="Votre mot de passe Nexah" id="nexah_password" />
                                            </div>
                                            <div class="mb-3">
                                                <label for="nexah_sender_id" class="form-label">Sender ID</label>
                                                <input :readonly="app_for === 'demo'" v-model="form.nexah_sender_id" :type="app_for === 'demo' ? 'password' : 'text'" class="form-control" placeholder="Ex: MerciE" id="nexah_sender_id" />
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-primary">{{ $t('save') }}</button>
                                                </div>
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
        <div>
            <!-- Success Message -->
            <div v-if="successMessage" class="custom-alert alert alert-success alert-border-left fade show" data="alert"
                id="alertMsg">
                <div class="alert-content">
                    <i class="ri-notification-off-line me-3 align-middle"></i> <strong>Success</strong> - {{
                        successMessage }}
                    <button type="button" class="btn-close btn-close-success" @click="dismissMessage"
                        aria-label="Close Success Message"></button>
                </div>
            </div>

            <!-- Alert Message -->
            <div v-if="alertMessage" class="custom-alert alert alert-danger alert-border-left fade show" data="alert"
                id="alertMsg">
                <div class="alert-content">
                    <i class="ri-notification-off-line me-3 align-middle"></i> <strong>Alert</strong> - {{ alertMessage
                    }}
                    <button type="button" class="btn-close btn-close-danger" @click="dismissMessage"
                        aria-label="Close Alert Message"></button>
                </div>
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
.rtl .custom-alert {
  max-width: 600px;
  float: left;
  top: -300px;
  right: 10px;
}
@media only screen and (max-width: 1024px) {
  .custom-alert {
  max-width: 600px;
  float: right;
  position: fixed;
  top: 90px;
  right: 20px;
}
.rtl .custom-alert {
  max-width: 600px;
  float: left;
  top: -230px;
  right: 10px;
}
}
</style>
