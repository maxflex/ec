interface Client {
  // columns
  id: number
  first_name: string | null
  last_name: string | null
  middle_name: string | null
  branches: unknown | null
  birthdate: string | null
  user_id: number | null
  head_teacher_id: number | null
  parent_first_name: string | null
  parent_last_name: string | null
  parent_middle_name: string | null
  passport_series: string | null
  passport_number: string | null
  passport_address: string | null
  passport_code: string | null
  passport_issued_date: string | null
  passport_issued_by: string | null
  fact_address: string | null
  created_at: string | null
  updated_at: string | null
}
type Clients = Client[]

interface Comment {
  // columns
  id: number
  user_id: number | null
  text: string
  entity_type: string
  entity_id: number
  created_at: string | null
  updated_at: string | null
}
type Comments = Comment[]

interface Phone {
  // columns
  id: number
  number: string
  comment: string | null
  is_verified: boolean
  is_parent: boolean
  entity_type: string
  entity_id: number
}
type Phones = Phone[]

interface ClientRequest {
  // columns
  id: number
  responsible_user_id: number | null
  status: RequestStatus
  grade: string | null
  google_id: string | null
  yandex_id: string | null
  ip: string | null
  comment: string | null
  user_id: number | null
  created_at: string | null
  updated_at: string | null
}
type Requests = ClientRequest[]

interface Teacher {
  // columns
  id: number
  first_name: string | null
  last_name: string | null
  middle_name: string | null
  subjects: unknown | null
  status: string
  desc: string | null
  photo_desc: string | null
  passport_series: string | null
  passport_number: string | null
  passport_address: string | null
  passport_code: string | null
  passport_issued_by: string | null
  so: unknown | null
  created_at: string | null
  updated_at: string | null
}
type Teachers = Teacher[]

interface User {
  // columns
  id: number
  first_name: string | null
  last_name: string | null
  middle_name: string | null
  created_at: string | null
  updated_at: string | null
}
type Users = User[]

// const RequestStatus = {
//   new: "new",
//   awaiting: "awaiting",
//   finished: "finished",
// } as const

type RequestStatus = "new" | "awaiting" | "finished"
