import { mdiFilePdfBox, mdiFilePowerpoint, mdiFileTableBox, mdiImage } from '@mdi/js'

export interface UploadedFileIcon {
  icon: string
  color: string
}

export type UploadFolder = 'lessons' | 'tests' | 'events' | 'violations' | 'teacher-contracts'

export function getIcon(file: UploadedFile): UploadedFileIcon {
  const index = file.name.lastIndexOf('.')
  const extension = file.name.slice(index + 1)
  switch (extension) {
    case 'pdf':
      return {
        icon: mdiFilePdfBox,
        color: '#e75a5a',
      }
    case 'ppt':
      return {
        icon: mdiFilePowerpoint,
        color: '#cb4f2e',
      }
    case 'gif':
    case 'svg':
    case 'png':
    case 'jpg':
    case 'jpeg':
      return {
        icon: mdiImage,
        color: '#BEAC83',
      }
    default:
      return {
        icon: mdiFileTableBox,
        color: '#6caf57',
      }
  }
}
