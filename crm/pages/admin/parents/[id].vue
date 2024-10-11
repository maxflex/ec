<script setup lang="ts">
const router = useRouter()
const route = useRoute()

const { data } = await useHttp<PersonResource>(`parents/${route.params.id}`)

router.replace({
  name: 'clients-id',
  params: {
    id: data.value!.id,
  },
})
</script>
