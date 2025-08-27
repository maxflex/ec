export interface ControlLkResource extends PersonResource {
  last_seen_at: string | null
  phones: PhoneResource[]
  logs_count: number
  tg_logs_count: number
  comments_count: number
  directions: ClientDirections
  representative: PersonResource & {
    phones: PhoneResource[]
    last_seen_at: string | null
    logs_count: number
    tg_logs_count: number
    reports_read_count: number
    reports_published_count: number
  }
}
