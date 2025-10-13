<?php

namespace App\Utils\TeacherStats;

class TeacherStatsItem
{
    // ОПОЗДАНИЯ ПРОВОДКИ

    /**
     * Количество проведенных занятий
     */
    public int $lessons_conducted = 0;

    /**
     * Количество занятий, проведенных с опозданием (не в дату занятия)
     */
    public int $lessons_conducted_next_day = 0;

    // ПОСЕЩАЕМОСТЬ

    /**
     * Суммарное количество детей, посетивших занятия в текущую дату (во всех статусах)
     */
    public int $client_lessons = 0;

    /**
     * Среднее количество учеников в проведенных занятиях
     */
    public float $client_lessons_avg = 0;

    /**
     * Количество пропусков, то есть "не был"
     */
    public int $client_lessons_absent = 0;

    /**
     * Количество опозданий, то есть "опоздал"+"опоздал дист."
     */
    public int $client_lessons_late = 0;

    /**
     * Количество удаленки, то есть "дист"+"опоздал дист."
     */
    public int $client_lessons_online = 0;

    /**
     * Доля пропусков, то есть количество "не был" поделить на суммарное количество посещений во всех статусах
     */
    public float $client_lessons_absent_share = 0;

    /**
     * Доля опозданий, то есть количество "опоздал"+"опоздал дист." поделить на суммарное количество посещений во всех статусах
     */
    public float $client_lessons_late_share = 0;

    /**
     * Доля удаленки, то есть количество "дист"+"опоздал дист." поделить на суммарное количество посещений во всех статусах
     */
    public float $client_lessons_online_share = 0;

    // УДЕРЖАНИЕ АУДИТОРИИ

    /**
     * Количество детей, начавших заниматься у преподавателя в конфигурации client_ID*teacher_ID*program*year
     */
    public int $retention_new = 0;

    /**
     * Количество детей, прекративших заниматься (определяется в дату последнего занятия в client_lessons) в конфигурации client_ID*teacher_ID*program*year
     */
    public int $retention_left = 0;

    /**
     * Удержание аудитории %
     */
    public float $retention_share = 0;

    /**
     * Ушло (100% – удержание аудитории%)
     */
    // public float $retention_left_share = 0;

    // ВЕДОМОСТЬ

    /**
     * Количество занятий, в которых дом.задание НЕ = NULL
     */
    public int $lessons_with_homework = 0;

    public float $lessons_with_homework_avg = 0;

    /**
     * Количество занятий, в которых есть хотя бы 1 прикрепленный файл
     */
    public int $lessons_with_files = 0;

    public float $lessons_with_files_avg = 0;

    /**
     * Количество выставленных оценок
     */
    public int $scores = 0;

    /**
     * Средняя оценка по занятиям
     */
    public float $scores_avg = 0;

    /**
     * Заполняемость оценок в процентах
     * (все статусы client_lessons, кроме "не был" * 3 - это 100%)
     */
    public float $scores_share = 0;

    /**
     * Сумма оценок по занятиям (для подсчета среднего)
     */
    public float $scores_sum = 0;

    /**
     * Количество оставленных комментариев к оценкам
     */
    public int $scores_comments = 0;

    /**
     * Количество оставленных общих комментариев
     */
    public int $comments = 0;

    // ОТЧЕТЫ

    /**
     * Количество отчетов в статусе опубликовано
     */
    public int $reports_published = 0;

    /**
     * Количество отчетов в статусе опубликовано без начисления
     */
    public int $reports_published_no_price = 0;

    /**
     * Средняя заполняемость отчетов
     */
    public float $reports_fill_avg = 0;

    /**
     * Средняя оценка по отчетам
     */
    public float $reports_grade_avg = 0;
}
