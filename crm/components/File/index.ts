import {
  mdiCodeJson,
  mdiCodeTags,
  mdiFileDocumentOutline,
  mdiFileMusicOutline,
  mdiFilePdfBox,
  mdiFilePowerpointBox,
  mdiFileTableBox,
  mdiFileVideoOutline,
  mdiFileWordBox,
  mdiFolderZip,
  mdiImage,
} from '@mdi/js'

export interface UploadedFileIcon {
  icon: string
  color: string
}

export type UploadFolder = 'lessons' | 'tests' | 'events' | 'violations' | 'teacher-contracts' | 'ai-prompts'

export function getIcon(file: UploadedFile): UploadedFileIcon {
  const index = file.name.lastIndexOf('.')
  const extension = index > -1 ? file.name.slice(index + 1).toLowerCase() : ''

  // Распространенные форматы, которые обычно прикрепляют в Gemini-контексте.
  switch (extension) {
    case 'pdf':
      return {
        icon: mdiFilePdfBox,
        color: '#e75a5a',
      }

    case 'doc':
    case 'docx':
    case 'odt':
    case 'rtf':
      return {
        icon: mdiFileWordBox,
        color: '#2a7de1',
      }

      // case 'xls':
      // case 'xlsx':
      // case 'ods':
      //   return {
      //     icon: mdiFileExcelBox,
      //     color: '#2e9d57',
      //   }

    case 'ppt':
    case 'pptx':
    case 'odp':
    case 'key':
      return {
        icon: mdiFilePowerpointBox,
        color: '#cb4f2e',
      }

    case 'gif':
    case 'svg':
    case 'png':
    case 'jpg':
    case 'jpeg':
    case 'webp':
    case 'bmp':
    case 'heic':
    case 'heif':
    case 'tif':
    case 'tiff':
      return {
        icon: mdiImage,
        color: '#BEAC83',
      }

    case 'txt':
    case 'md':
    case 'markdown':
      return {
        icon: mdiFileDocumentOutline,
        color: '#7b8895',
      }

    case 'json':
      return {
        icon: mdiCodeJson,
        color: '#f0b429',
      }

    case 'xml':
    case 'html':
    case 'htm':
    case 'css':
    case 'js':
    case 'mjs':
    case 'cjs':
    case 'ts':
    case 'tsx':
    case 'jsx':
    case 'py':
    case 'php':
    case 'java':
    case 'c':
    case 'cpp':
    case 'cc':
    case 'go':
    case 'rb':
    case 'sh':
    case 'sql':
    case 'yaml':
    case 'yml':
      return {
        icon: mdiCodeTags,
        color: '#5c6bc0',
      }

    case 'mp3':
    case 'wav':
    case 'aac':
    case 'ogg':
    case 'flac':
    case 'm4a':
      return {
        icon: mdiFileMusicOutline,
        color: '#8f5cc2',
      }

    case 'mp4':
    case 'mpeg':
    case 'mpg':
    case 'mov':
    case 'avi':
    case 'webm':
    case 'wmv':
    case '3gp':
    case 'm4v':
    case 'mkv':
      return {
        icon: mdiFileVideoOutline,
        color: '#cc5f3d',
      }

    case 'zip':
    case 'rar':
    case '7z':
    case 'tar':
    case 'gz':
      return {
        icon: mdiFolderZip,
        color: '#212121',
      }

    default:
      return {
        icon: mdiFileTableBox,
        color: '#6caf57',
      }
  }
}
