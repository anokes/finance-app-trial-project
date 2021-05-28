<template>
  <div>
    <div
      v-if="show"
      class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex"
    >
      <div class="relative w-auto my-6 mx-auto max-w-3xl">
        <!--content-->
        <div
          class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none"
        >
          <!--header-->
          <div
            class="flex items-start justify-between p-4 border-b border-solid border-blueGray-200 rounded-t"
          >
            <h3 class="text-xl font-bold">Add Balance Entry</h3>
            
          </div>

          <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="mt-2 md:mt-0 md:col-span-3">
                <form @submit.prevent="submit" id="entry-form">
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
                            v-model="fields.label"
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
                            v-model="fields.entry_date"
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
                            v-model="fields.amount"
                          />
                          <div
                            v-if="errors && errors.amount"
                            class="text-danger"
                          >
                            {{ errors.amount[0] }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                      <div v-if="entrySuccess" class="text-sm font-bold text-green-600 float-left">
                          Balance entry success! Continue adding more items or close the form.
                      </div>
                      <button
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-blue-600 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:gray-200"
                        type="button"
                        v-on:click="toggleModal()"
                      >
                        Close
                      </button>
                      <button
                        type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-blue-700 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-300"
                      >
                        Save Entry
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
    <div v-if="show" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
  </div>
</template>

<script>
export default {
  name: "add-entry-modal",
  props: ["show"],
  data() {
    return {
      showModal: this.show,
      fields: {},
      errors: {},
      entrySuccess: false,
    };
  },
  methods: {
    toggleModal: function () {
      //clear message
      this.entrySuccess = false;

      //send toggle data back to the parent
      this.$emit("toggle");
    },
    submit() {
      this.errors = {};
      axios
        .post("/submit", this.fields)
        .then((response) => {

            if(response.status == 201) {
                alert('success');
                //clear the fields array
                this.entrySuccess = true;
                this.fields = {};
                this.$parent.getBalanceEntries()
            } else {
                alert('failed');
            }
        })
        .catch((error) => {
            console.log(error);
          if (error.response.status === 422) {
            this.errors = error.response.data.errors || {};
          }
        });
    },
  },
};
</script>