<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewTeacherMail extends Mailable
{
    use Queueable, SerializesModels;

    public $params;

    public UploadedFile $file;

    public $subjects;

    public function __construct($params)
    {
        $this->params = $params;
        $this->file = $params['file'];
        $this->subjects = $params['subjects'] ? array_map(function ($id) {
            $id = intval($id);
            switch ($id) {
                case 1:
                    return 'математика';
                case 2:
                    return 'физика';
                case 3:
                    return 'химия';
                case 4:
                    return 'биология';
                case 5:
                    return 'информатика';
                case 6:
                    return 'русский язык';
                case 7:
                    return 'литература';
                case 8:
                    return 'обществознание';
                case 9:
                    return 'история';
                case 10:
                    return 'английский язык';
                case 11:
                    return 'география';
                case 12:
                    return 'сочинение';
                default:
                    return 'итоговое собеседование';
            }
        }, explode(',', $params['subjects'])) : [];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Анкета преподавателя',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-teacher',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->file->getRealPath())
                ->as($this->file->getClientOriginalName())
                ->withMime($this->file->getMimeType()),
        ];
    }
}
