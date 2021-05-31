<template>
  <div class="w-100">
    <div
      class="inset-0 z-50 outline-none focus:outline-none justify-center items-center flex"
    >
      <div class="relative w-full mt-2 mx-auto max-w-3xl" @mouseenter="activeHover = true" @mouseleave="activeHover = false">
        <!--content-->
        <div
          class="border-0 rounded-lg relative flex flex-col w-full bg-white outline-none focus:outline-none"
          
        >
          <!--header-->
          <div
            class="md:grid md:grid-cols-4 md:gap-6 p-4 border-b border-solid border-blueGray-200 rounded-t w-100"
            
          >
            <h3 class="text-xl font-bold float-left">{{ itemData.label }}</h3>
            <h3
              class="float-right text-right text-lg text-blue-600 hover:underline font-semibold"
              @click="toggle = !toggle"
            >
              <span v-if="activeHover">Edit</span>
            </h3>
            <h3
              class="float-right text-right text-lg text-blue-600 hover:underline font-semibold"
              @click="this.delete"
            >
              <span v-if="activeHover">Delete</span>
            </h3>
            <h3 class="text-xl font-bold float-right text-right">
              {{ Number(itemData.amount).toLocaleString() }}
            </h3>
          </div>

          <div v-show="toggle" class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="mt-2 md:mt-0 md:col-span-3">
                <form @submit.prevent="update" id="entry-form">
                  <div class="shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 bg-white sm:p-6">
                      <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                          <label
                            for="label"
                            class="block text-sm font-bold text-gray-700"
                            >Label</label
                          >
                          <input
                            type="text"
                            name="label"
                            id="label"
                            class="mt-1 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            v-model="balanceItemForm.label"
                          />
                          <div
                            v-if="errors && errors.label"
                            class="text-danger"
                          >
                            {{ errors.label[0] }}
                          </div>
                        </div>

                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                          <label
                            for="date"
                            class="block text-sm font-bold text-gray-700"
                            >Date</label
                          >
                          <input
                            type="datetime-local"
                            name="date"
                            id="date"
                            class="mt-1 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            v-model="balanceItemForm.entry_date"
                          />
                          <div
                            v-if="errors && errors.entry_date"
                            class="text-danger"
                          >
                            {{ errors.entry_date[0] }}
                          </div>
                        </div>

                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                          <label
                            for="amount"
                            class="block text-sm font-bold text-gray-700"
                            >Amount</label
                          >
                          <input
                            type="text"
                            name="amount"
                            id="amount"
                            autocomplete="postal-code"
                            class="mt-1 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            placeholder="$0.00"
                            v-model="balanceItemForm.amount"
                          />
                          <div
                            v-if="errors && errors.amount"
                            class="text-danger"
                          >
                            {{ errors.amount[0] }}
                          </div>
                        </div>
                        <input type="hidden" name="user_id" :value="balanceItemForm.user_id">
                        <div
                            v-if="errors && errors.user_id"
                            class="text-danger"
                          >
                            {{ errors.user_id[0] }}
                          </div>
                      </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                      <div
                        v-if="updateSuccess"
                        class="text-sm font-bold text-green-600 float-left"
                      >
                        Balance update success!
                      </div>
                      <button
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-blue-600 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:gray-200"
                        type="button"
                        @click="toggle = !toggle"
                      >
                        Close
                      </button>
                      <button
                        type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-blue-700 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-300"
                        v-on:click="isHidden = false"
                      >
                        Update Entry
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "balance-item",
  props: ["balanceItem"],
  data() {
    return {
      itemData: this.balanceItem,
      updateSuccess: false,
      errors: {},
      toggle: false,
      balanceItemForm: {
        label: this.balanceItem.label,
        entry_date: this.balanceItem.entry_date,
        amount: this.balanceItem.amount,
        id: this.balanceItem.id,
        user_id: this.balanceItem.user_id
      },
      activeHover: false,
    };
  },
  methods: {
    toggleModal: function () {
      //clear message
      this.updateSuccess = false;

      //refresh parent data
      this.$parent.getBalanceEntries();
    },
    update() {
      this.errors = {};
      axios
        .post("/update", this.balanceItemForm)
        .then((response) => {
          if (response.status == 201) {
            this.updateSuccess = true;
            this.$parent.getBalanceEntries();
            this.itemData = this.balanceItemForm;
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
    delete() {
      if (confirm("Do you really want to delete?")) {
        this.errors = {};
        axios
          .post("/delete", this.balanceItemForm)
          .then((response) => {
            if (response.status == 201) {
              this.$parent.getBalanceEntries();
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
      }
    },
  },
  
};
</script>