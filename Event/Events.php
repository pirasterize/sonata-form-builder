<?php

namespace Pirastru\FormBuilderBundle\Event;

class Events
{
    public const FORM_DATA_PRE_FORMAT = 'pirastru.formbuilder.event.form_data.pre_format';
    public const SUBMISSION_PRE_SAVE = 'pirastru.formbuilder.event.submission.pre_save';
    public const PRE_SEND_MAIL = 'pirastru.formbuilder.event.mail';
}