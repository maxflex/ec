const teachers = ref<TeacherListResource[]>()

export default function () {
  async function loadData() {
    const { data } = await useHttp<ApiResponse<TeacherListResource>>(`teachers`)
    if (data.value) {
      teachers.value = data.value.data
    }
  }
  if (teachers.value === undefined) {
    loadData()
  }
  return teachers
}
