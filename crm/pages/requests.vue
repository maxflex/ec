<script setup>
const response = ref()

const loadData = async function () {
  // await new Promise((r) => setTimeout(r, 1))
  // await nextTick()
  // const { data } = await useHttp("requests", {
  //   method: "GET",
  // })
  // console.log(data, data.value)
  const {
    public: { baseUrl },
  } = useRuntimeConfig()
  const { data, error } = await useFetch("requests", {
    method: "GET",
    baseURL: baseUrl,
    // headers: { Authorization: `Bearer ${token}` },
  })
  console.log(data, error)
  response.value = data.value
}

onMounted(async () => {
  await loadData()
})
</script>
<template>
  <h1>requests</h1>
  <RequestItem />
  <v-btn @click="loadData()"> load data </v-btn>
  <code>
    {{ response }}
  </code>
</template>
