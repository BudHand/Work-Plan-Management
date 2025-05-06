<template>
  <layout>
    <div class="col-md-12">
      <div class="row">
        <div class="col-md-3" v-if="user && user.role === 'Admin'">
          <div class="small-box">
            <div class="inner">
              <h3>{{ total_members }}</h3>
              <p>Total Members</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="small-box">
            <div class="inner">
              <h3>{{ total_projects }}</h3>
              <p>Total Work Plans</p>
            </div>
            <div class="icon">
              <i class="fa fa-th-list"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="card card-outline card-secondary">
            <div class="card-body p-0 ml-2 mr-2 mt-3">
              <Toolbar class="p-mb-4">
                <template #left>
                  <div class="p-field">
                    <Dropdown v-model="selectYear" :options="uniqueYears" optionLabel="year" optionValue="year" 
                      @change="onSelectYearChange" style="font-size: 14px; width: 140px;" />
                    <Dropdown v-model="selectStatus" :options="statusOptions" optionLabel="label" optionValue="value" 
                      @change="onSelectStatusChange" style="font-size: 14px; width: 160px; margin-left: 10px;" />
                    <Dropdown v-model="selectName" :options="filteredProjects" optionLabel="name" optionValue="id" 
                      @change="onSelectNameChange" style="font-size: 14px; width: 400px; margin-left: 10px;" />
                  </div>
                </template>
                <template #right>
                  <label style="margin-right: 10px;">Progress (%): </label>
                  <InputText type="text" v-model="selectProgress" style="font-size: 25px; text-align: center; width: 68px; height: 50px;" readonly />
                </template>
              </Toolbar>

              <div class="row">
                <div class="col-md-6">
                  <apexchart ref="barChart" type="bar" :options="barChartOptions" :series="barChartSeries" />
                </div>
                <div class="col-md-6">
                  <apexchart ref="lineChart" type="line" :options="lineChartOptions" :series="lineChartSeries" />
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </layout>
</template>

<script>
import Layout from "../../Partials/Layout";
import ErrorsAndMessages from "../../Partials/ErrorsAndMessages";
import { usePage } from "@inertiajs/inertia-vue3";
import VueApexCharts from "vue3-apexcharts";
import axios from 'axios';
import { computed, ref } from "vue";

export default {
  name: "Posts",
  components: {
    ErrorsAndMessages,
    Layout,
    apexchart: VueApexCharts
  },
  data() {
    return {
      selectYear: "",
      selectStatus: "",
      selectName: "",
      selectWorkPlan: "",
      selectProgress: "",
      statusOptions: [
        { label: 'On progress', value: 'On progress' },
        { label: 'Overdue', value: 'Overdue' },
        { label: 'Completed', value: 'Completed' }
      ],
      // Bar Chart
      barChartSeries: [],
      barChartOptions: {
        barChart: {
          type: 'bar'
        },
        xaxis: {
          categories: [],
        },
        fill: {
          opacity: 1
        },
        colors: ['#2d4353', '#ff0008'],
        dataLabels: {
          enabled: false
        },
      },

      // Line Chart
      lineChartSeries: [],
      lineChartOptions: {
        lineChart: {
          type: 'line'
        },
        stroke: {
          curve: 'smooth',
        },
        xaxis: {
          categories: [],
        },
        fill: {
          opacity: 1
        },
        colors: ['#2d4353', '#ff0008'],
        dataLabels: {
          enabled: false
        },
      }
    };
  },
  setup() {
    const user = computed(() => usePage().props.value.auth.user);
    const projects = usePage().props.value.projects;

    // Set Opsi Years Work Plan
    const uniqueYears = computed(() => {
      const years = projects.map(project => project.year);
      const uniqueYearsSet = new Set(years);
      return Array.from(uniqueYearsSet).map(year => ({ year })).sort((a, b) => b.year - a.year);
    });

    const selectYear = ref("");
    const selectStatus = ref("");
    
    // Set Opsi Names Work Plan
    const filteredProjects = computed(() => {
      if (!selectYear.value) return projects;
      return projects.filter(project => project.year === selectYear.value);
    });

    return {
      user,
      projects,
      uniqueYears,
      selectYear,
      selectStatus,
      filteredProjects
    }
  },
  props: {
    total_projects: Number,
    total_members: Number
  },
  mounted() {
    this.setDefaultDropdownYear()
  },
  methods: {
    setDefaultDropdownYear() {
      if (this.uniqueYears && this.uniqueYears.length > 0) {
        const latestYear = this.uniqueYears[0].year;
        this.selectYear = latestYear;
        this.setDefaultDropdownStatus();
        this.setDefaultDropdownName();
      }
    },
    setDefaultDropdownStatus() {
      this.selectStatus = "On progress"; 
    },
    setDefaultDropdownName() {
      if (this.filteredProjects && this.filteredProjects.length > 0) {
        // Mengurutkan proyek berdasarkan ID dari terbesar ke terkecil
        const sortedProjects = this.filteredProjects.sort((a, b) => b.id - a.id);
        // Mengambil project dengan ID terbesar (terbaru)
        const latestProject = sortedProjects[0];
        // Mengatur nilai default untuk selectName
        this.selectName = latestProject.id;
        // Memanggil fetchChartData dengan ID proyek terbaru
        this.fetchChartData({ value: latestProject.id });
      }
    },
    async fetchChartData(item) {
      try {
        const year = this.selectYear;
        const status = this.selectStatus;
        const code_workplans = this.selectName;  

        // Bar Chart
        const resBarChart = await axios.get(`/chart_data_1`, { params: { year, status, code_workplans } });
        this.barChartSeries = resBarChart.data.dataSets.map(dataSet => ({
          name: dataSet.nama,
          data: dataSet.data,
        }));

        // Line Chart
        const resLineChart = await axios.get(`/chart_data_2`, { params: { year, status, code_workplans } });
        this.lineChartSeries = resLineChart.data.dataSets.map(dataSet => ({
          name: dataSet.nama,
          data: dataSet.data,
        }));

        // Update Grafik
        this.$refs.barChart.updateOptions({
          xaxis: {
            categories: resBarChart.data.categories
          }
        });
        this.$refs.lineChart.updateOptions({
          xaxis: {
            categories: resLineChart.data.categories
          }
        });

        // Mengambil Data Progress
        const resProgress = await axios.get(`/dashboard/progress_projects`, { params: { code_workplans } });
        this.selectProgress = resProgress.data.progress; 
      } catch (error) {
        console.error('Error fetching chart data:', error);
      }
    },
    onSelectYearChange() {
      this.setDefaultDropdownName();
      this.fetchChartData();
    },
    onSelectStatusChange() {
      this.fetchChartData();
    },
    onSelectNameChange() {
      this.fetchChartData();
    }
  },
}
</script>