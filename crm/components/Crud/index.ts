export interface CrudDialogData {
  width: number
  saving: Ref<boolean>
  isEditing: ComputedRef<boolean>
  save: () => void
}
