export interface CrudDialogData {
  width: number
  saving: boolean
  isEditing: ComputedRef<boolean>
  save: () => void
}
