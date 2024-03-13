import { defineStore } from "pinia"

export const useMainStore = defineStore("main", {
  state: () => ({
    user: null as User | null,
  }),
  // actions: {
  //   increment() {
  //     // `this` is the store instance
  //     this.counter++
  //   },
  // },
})
