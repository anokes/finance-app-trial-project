<template>
  <div class="bg-gray-100">
    <div class="row justify-content-center bg-blue-900 h-28 mb-10">
      <div class="col-md-8">
        <div class="mt-10 mr-2 text-white font-bold text-2xl float-left">
          Your Balance
        </div>
        <button
          class="mt-10 mx-2 bg-blue-700 hover:bg-blue-500 h-10 text-white font-bold py-2 px-4 rounded float-left"
          v-on:click="toggleShowModal()"
        >
          <svg
            class="float-left mt-1"
            xmlns="http://www.w3.org/2000/svg"
            width="14"
            height="14"
            viewBox="0 0 14 14"
          >
            <g fill="#FFF">
              <polygon points="8 0 8 14 6 14 6 0" />
              <polygon points="14 6 14 8 0 8 0 6" />
            </g>
          </svg>
          &nbsp;Add Entry
        </button>
        <button
          class="mt-10 mx-2 bg-blue-700 hover:bg-blue-500 h-10 text-white font-bold py-2 px-4 rounded"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="34"
            height="37"
            viewBox="0 0 34 37"
            class="float-left -mt-1"
          >
            <defs>
              <filter
                id="import-a"
                width="121.8%"
                height="175%"
                x="-10.9%"
                y="-37.5%"
                filterUnits="objectBoundingBox"
              >
                <feOffset dy="1" in="SourceAlpha" result="shadowOffsetOuter1" />
                <feGaussianBlur
                  in="shadowOffsetOuter1"
                  result="shadowBlurOuter1"
                  stdDeviation="3"
                />
                <feColorMatrix
                  in="shadowBlurOuter1"
                  result="shadowMatrixOuter1"
                  values="0 0 0 0 0   0 0 0 0 0   0 0 0 0 0  0 0 0 0.3 0"
                />
                <feMerge>
                  <feMergeNode in="shadowMatrixOuter1" />
                  <feMergeNode in="SourceGraphic" />
                </feMerge>
              </filter>
            </defs>
            <g fill="#FFF" filter="url(#import-a)" transform="translate(-1 1)">
              <path
                d="M4,13 L10,13 L10,7 L14,7 L7,0 L0,7 L4,7 L4,13 Z M0,15 L14,15 L14,17 L0,17 L0,15 Z"
                transform="translate(11 9)"
              />
            </g>
          </svg>
          &nbsp;Import CSV
        </button>
        <div class="pt-8 text-gray-400 font-bold float-right text-sm">
          TOTAL BALANCE<br /><span class="text-green-500 text-3xl font-thin"
            >${{
              Number(this.balanceData.totalAmount[0].amountSum).toLocaleString()
            }}</span
          >
        </div>
      </div>
    </div>

    <div
      class="row justify-content-center min-h-20"
      v-for="dailyItem in balanceData.balanceDailyTotal.data"
      :key="dailyItem.date"
    >
      <div class="col-md-8">
        <div class="row justify-content-between">
          <div class="mt-10 mr-2 text-gray-400 font-bold text-lg float-left">
            {{ dailyItem.date }}
          </div>
          <!-- show colour based on positive/negative-->
          <div
            :class="
              dailyItem.amountSum >= 0
                ? 'mt-10 mr-2 text-green-600 font-bold text-lg float-right'
                : 'mt-10 mr-2 text-red-600 font-bold text-lg float-right'
            "
          >
            <span v-if="dailyItem.amountSum > 0">+</span
            >{{ Number(dailyItem.amountSum).toLocaleString() }}
          </div>
        </div>
        <div
          class="row"
          v-for="item in filteredBalanceItems(dailyItem)"
          :key="item.id"
        >
          <balance-item :balanceItem="item"></balance-item>
        </div>
      </div>
    </div>
    <div class="row justify-content-center my-10">
      <pagination
        :data="paginationData"
        @pagination-change-page="getResults"
      ></pagination>
    </div>

    <!-- Include "Entry Form Component" with "toggle" to be invoked by child to manage the modal's state -->
    <add-entry-form
      :show="showModal"
      @toggle="toggleShowModal"
    ></add-entry-form>
  </div>
</template>

<script>
import AddEntryForm from "./AddEntryForm.vue";
export default {
  components: { AddEntryForm },
  data() {
    return {
      showModal: false,
      balanceData: {
        balanceItems: [],
        balanceDailyTotal: [],
        totalAmount: 0,
      },
      paginationData: null,
    };
  },
  methods: {
    //toggle data
    toggleShowModal() {
      this.showModal = !this.showModal;
      this.getBalanceEntries();
    },
    getResults(page) {
      if (typeof page === "undefined") {
        page = 1;
      }

      axios
        .get("/show?page=" + page)
        .then((response) => {
          if (response.status == 200) {
            console.log(response);
            //assign returned data to our props
            this.balanceData.balanceItems = response.data.items;
            this.balanceData.balanceDailyTotal = response.data.daily_totals;
            this.balanceData.totalAmount = response.data.total_amount;
            this.paginationData = response.data.daily_totals;
          } else {
            alert("failed");
          }
        })
        .catch((error) => {
          console.log(error);
          if (error.response.status === 422) {
            this.errors = error.response.data.errors || {};
          }
        });
    },
    getPaginationObject() {
      return Object.assign({}, this.balanceData.balanceDailyTotal.data);
    },
    getBalanceEntries() {
      this.errors = {};
      axios
        .get("/show")
        .then((response) => {
          if (response.status == 200) {
            //assign returned data to our props
            this.balanceData.balanceItems = response.data.items;
            this.balanceData.balanceDailyTotal = response.data.daily_totals;
            this.balanceData.totalAmount = response.data.total_amount;
          } else {
            alert("failed");
          }
        })
        .catch((error) => {
          console.log(error);
          if (error.response.status === 422) {
            this.errors = error.response.data.errors || {};
          }
        });
    },
    filteredBalanceItems(dailyRecord) {
      //create a list of all the balanceItems to be filteres
      var list = Object.values(this.balanceData.balanceItems);

      //filter the list of all balanceItems, using the passed in date from the parent v-for and return the list so we are only showing entries for the matching day
      return list.filter((item) => {
        return item.date.match(dailyRecord.date);
      });
    },
    getDateLabel(date) {
      var existing = new Date(date);
      var today = new Date();

      var sameDate = this.isSameDate(existing, today);

      if (sameDate) {
        return "Today";
      } else {
        return date;
      }
    },
  },

  mounted() {
    //this.getBalanceEntries();
    this.getResults();
    console.log("Component mounted.");
  },
};
</script>