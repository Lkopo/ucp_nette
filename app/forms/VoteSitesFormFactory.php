<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 20.08.2017
 * Time: 12:25
 */

namespace App\Forms;

use App\Model\VoteManager;
use Nette\Application\UI\Form;

class VoteSitesFormFactory extends BaseFormFactory
{
    /** @var VoteManager */
    private $voteManager;

    public function __construct(VoteManager $voteManager)
    {
        $this->voteManager = $voteManager;
    }

    public function create($editing = FALSE)
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('name', 'forms.vote_sites.name')
            ->setRequired('forms.vote_sites.name_empty');

        $form->addText('url', 'forms.vote_sites.url')
            ->setRequired('forms.vote_sites.url_empty')
            ->addRule(Form::URL, 'forms.vote_sites.url_valid');

        $form->addText('check_url', 'forms.vote_sites.check_url')
            ->setRequired('forms.vote_sites.check_url_empty')
            ->addRule(Form::URL, 'forms.vote_sites.check_url_valid');

        $form->addGroup('');

        $form->addText('image_path', 'forms.vote_sites.image_path')
            ->setOption('description', 'forms.vote_sites.image_path_description');

        $form->addText('image_width', 'forms.vote_sites.image_width')
            ->setRequired(FALSE)
            ->setOption('description', 'forms.vote_sites.image_width_description')
            ->addRule(Form::INTEGER, 'forms.vote_sites.image_width_numeric')
            ->addRule(Form::MIN, 'forms.vote_sites.image_width_min', 0);

        $form->addText('image_height', 'forms.vote_sites.image_height')
            ->setRequired(FALSE)
            ->setOption('description', 'forms.vote_sites.image_height_description')
            ->addRule(Form::INTEGER, 'forms.vote_sites.image_height_numeric')
            ->addRule(Form::MIN, 'forms.vote_sites.image_height_min', 0);
        $form->addGroup('');

        $form->addText('points', 'forms.vote_sites.points')
            ->setRequired('forms.vote_sites.points_empty')
            ->addRule(Form::INTEGER, 'forms.vote_sites.points_numeric')
            ->addRule(Form::MIN, 'forms.vote_sites.points_min', 0);

        $form->addText('cooldown', 'forms.vote_sites.cooldown')
            ->setRequired('forms.vote_sites.cooldown_empty')
            ->setOption('description', 'forms.vote_sites.cooldown_description')
            ->addRule(Form::INTEGER, 'forms.vote_sites.cooldown_numeric')
            ->addRule(Form::MIN, 'forms.vote_sites.cooldown_min', 0);

        $form->addText('script_id', 'forms.vote_sites.script_id')
            ->setRequired('forms.vote_sites.script_id_empty')
            ->setOption('description', 'forms.vote_sites.script_id_description')
            ->addRule(Form::INTEGER, 'forms.vote_sites.script_id_numeric');

        $form->addGroup('');

        $form->addSubmit('submit', ($editing ? 'forms.vote_sites.confirm' : 'forms.vote_sites.add'))
            ->setAttribute('class', 'btn-lg btn-success btn-block');

        $form->addProtection('forms.global.csrf_protection');

        return $form;
    }
}