import type { ClientDirection } from '../Client'

export interface PeopleSelectorResource extends PersonResource {
  directions?: ClientDirection[]
  event_participant?: {
    id: number
    confirmation: EventParticipantConfirmation
  }

  // нужно только на /group-message
  representative?: PersonResource & HasPhones
  phones?: PhoneResource[]
}

export const PeopleSelectorLabel = {
  [EntityTypeValue.client]: 'Клиенты',
  [EntityTypeValue.teacher]: 'Преподаватели',
}
