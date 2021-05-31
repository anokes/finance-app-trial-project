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
            <h3 class="text-xl font-bold">Import Balance Entries</h3>
          </div>

          <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="mt-2 md:mt-0 md:col-span-3">
                <form @submit.prevent="submit" id="import-form">
                  <div class="shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 bg-white sm:p-6">
                      <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-6 lg:col-span-3">
                          <label
                            for="label"
                            class="block text-sm font-bold text-gray-700"
                            >.CSV FILE</label
                          >
                          <input
                            type="file"
                            id="file"
                            ref="file"
                            v-on:change="handleFileUpload()"
                            class="mt-1 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                          />
                          <div
                            v-if="errors && errors.file"
                            class="text-danger"
                          >
                            {{ errors.file[0] }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                      <div
                        v-if="importSuccess"
                        class="text-sm font-bold text-green-600 float-left"
                      >
                        Balance entry success! Continue adding more items or
                        close the form.
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
                        Import
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
  name: "import-entries-modal",
  props: ["show"],
  data() {
    return {
      showImportModal: this.show,
      file: null,
      errors: {},
      importSuccess: false,
    };
  },
  methods: {
    handleFileUpload() {
      this.file = this.$refs.file.files[0];
    },
    toggleModal: function () {
      //clear message
      this.importSuccess = false;

      //send toggle data back to the parent
      this.$emit("toggle");
    },
    submit() {
      //create a FormData object to handle the file
      let formData = new FormData();

      //add the file to the form data
      formData.append("file", this.file);

      this.errors = {};

      axios
        .post("/import", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          if (response.status == 200) {
            
            //set the main page to show the progress indicator/disable the buttons as well as show the line count
            this.$parent.importInProgress = true;
            this.$parent.importLineCount = response.data.lineCount;
            
            //close the modal
            this.toggleModal();
          } else {
            alert("failed");
          }
        })
        .catch((error) => {
          console.log(error.response);
          if (error.response.status === 422) {
            this.errors = error.response.data.errors || {};
          }
        });
    },
  },
};
</script>