<template>
    <layout title="Work Plan List">
    <Toast position="top-center" />

        <div class="card">
            <Toolbar class="p-mb-4">
                <template #right>
                    <span>
                        <InputText placeholder="Search..." v-model="search" style="font-size: 13px;" />
                        <Button icon="pi pi-search" iconPos="right"  class="p-button-sm"  @click="onSearch"/>
                    </span>
                </template>
            </Toolbar>

            <DataTable :value="projects" :lazy="true" :paginator="true" :rows="dataPerPage" ref="dt"
                :totalRecords="totalData" :loading="loading"  @page="onPage($event)" responsiveLayout="scroll">
                <Column field="" header="No">
                    <template #body="slotProps">
                        {{ ((lazyParams.page - 1) * dataPerPage) + slotProps.index + 1 }}
                    </template>
                </Column>
                <Column field="code_workplans" header="Code"></Column>
                <Column header="Work Plans" style="max-width: 200px;">
                    <template #body="slotProps">
                        {{ slotProps.data.name }}
                        <br>
                    <small>
                        <b>Description: </b>
                        <br>
                        {{ slotProps.data.description }}
                    </small>
                    </template>
                </Column>
                <Column field="project_type" header="Type"></Column>
                <Column field="team_leader" header="PIC">
                    <template #body="slotProps">
                        {{ slotProps.data.team_leader.name }}
                        <br>
                    <small>
                        <b>Team Member/s: </b>
                        <div v-for="member in slotProps.data.team_members">
                            - {{ member.name }}
                        </div>
                    </small>
                    </template>
                </Column>
                <Column field="year" header="Year"></Column>
                <Column header="Budget">
                    <template #body="slotProps">
                        {{ _formatRupiah(slotProps.data._budget) }}
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <span :class="['status-badge', getStatusBadgeClass(slotProps.data.status)]">
                            {{ slotProps.data.status }}
                        </span>
                    </template>
                </Column>
                <Column header="Validation">
                    <template #body="slotProps">
                        {{ slotProps.data.validation }}
                        <br>
                    <small v-if="slotProps.data.validation !== null">
                        <b>Note: </b>
                        <br>
                        {{ slotProps.data.note ? slotProps.data.note : '-'  }}
                    </small>
                    </template>
                </Column>
                <Column :exportable="false" header="Action">
                    <template #body="slotProps">
                        <Button @click="onEdit(slotProps.data)" icon="pi pi-pencil" class="p-button-rounded p-button-primary" :title="'Edit'" style="margin-right: 5px;" 
                            v-if="user.role === 'Kabag' && slotProps.data.status === 'Created'" />
                        <Button @click="onSubmit(slotProps.data)" icon="pi pi-send" class="p-button-rounded p-button-success" :title="'Submit'" style="margin-right: 5px;" 
                            v-if="user.role === 'Kabag' && slotProps.data.status === 'Created'" />
                        <Button @click="onView(slotProps.data)" icon="pi pi-eye" class="p-button-rounded p-button-warning" v-if="slotProps.data.status === 'On progress' || slotProps.data.status === 'Completed'" :title="'View'" />
                    </template>
                </Column>
                <template #empty>
                    No records found
                </template>
            </DataTable>
        </div>
    </layout>
</template>

<script>
import Layout from "../../Partials/Layout";
import ErrorsAndMessages from "../../Partials/ErrorsAndMessages";
import { usePage } from "@inertiajs/inertia-vue3";
import { computed } from "vue";
import { formatRupiah } from "../../utils/index.js";
import { pageListWorkplans, updateStatusSubmitted } from '../../Api/workplans.api.js';

export default {
    name: "ListWorkplans",
    components: {
        ErrorsAndMessages,
        Layout,
    },
    setup() {
        const user = computed(() => usePage().props.value.auth.user);
        return {
            user
        }
    },
    data() {
        return {
            projects: [],
            display: false,
            dataPerPage: 10, 
            totalData: 0, 
            error: {},
            lazyParams: {
                page: 1
            },
            loading: false
        };
    },
    props: {
        errors: Object,
    },
    methods: {
        async loadLazyData() {
            this.loading = true;
            var response = await pageListWorkplans ({ page : this.lazyParams.page, search: this.search });
            this.projects = response.data.data.data;
            this.totalData = response.data.data.total;
            this.loading = false;
        },
        _formatRupiah(val){
            return formatRupiah(val)
        },
        onSearch(){
            this.totalData = 0;
            this.loadLazyData();
        },
        onPage(event) {
            this.lazyParams.page = event.page + 1;
            this.loadLazyData();
        },
        onEdit(data) {
        this.$inertia.visit(`/workplans/list/edit_workplan?id=${data.id}`);
        },
        async onSubmit(item){
            this.loading = true;
            var response = await updateStatusSubmitted(item);
            this.loading = false;
            this.loadLazyData();
        },
        onView(data) {
        this.$inertia.visit(`/workplans/list/view_workplan?id=${data.id}`);
        },
        getStatusBadgeClass(status) {
            switch (status) {
                case "Created":
                return "badge badge-secondary";
                case "Submitted":
                return "badge badge-warning";
                case "On progress":
                return "badge badge-primary";
                case "Overdue":
                return "badge badge-danger";
                case "Rejected":
                return "badge badge-dark";
                case "Completed":
                return "badge badge-success";
                default:
                return "badge";
            }
        },
    },
    mounted() {
        this.projects = this.$page.props.projects.data; 
        this.totalData = this.$page.props.projects.total;
    }
};

</script>

<style scoped>
.custom-select {
    font-size: 14px;
}
.status-badge {
    min-width: 80px; 
}
</style>
