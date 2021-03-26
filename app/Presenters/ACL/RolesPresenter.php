<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Controls\Bootstrap\Card;
use App\Controls\Bootstrap\CardForm;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Contributte\FormsBootstrap\Inputs\SelectInput;
use Models\Entities\Role\Role;
use Models\Entities\Rule;
use Models\Entities\Rule\RuleFactory;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Repositories\PrivilegeRepository;
use Repositories\ResourceRepository;
use Repositories\RoleRepository;
use Repositories\RuleRepository;

final class RolesPresenter extends DefaultPresenter
{
	private RoleRepository $repository;
	private ResourceRepository $resourceRepository;
	private PrivilegeRepository $privilegeRepository;
	private RuleRepository $ruleRepository;
	private RuleFactory $ruleFactory;

	public function __construct(
		RoleRepository $roleRepository,
		ResourceRepository $resourceRepository,
		PrivilegeRepository $privilegeRepository,
		RuleRepository $ruleRepository,
		RuleFactory $ruleFactory
	) {
		$this->repository = $roleRepository;
		$this->resourceRepository = $resourceRepository;
		$this->privilegeRepository = $privilegeRepository;
		$this->ruleRepository = $ruleRepository;
		$this->ruleFactory = $ruleFactory;
	}

	public function actionDefault()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function renderDefault(): void
	{
		$roles = $this->repository->findAll();
		$this->template->roles = $roles;
	}

	public function renderEdit(int $roleId)
	{
		$role = $this->repository->findById($roleId, true);
		if (!$role) {
			$this->error('Role not found');
		}
		$this->template->role = $role;
	}

	public function handleEditRole(int $roleId)
	{
		$roleId = (int) $this->getParameter('roleId');
		$role = $this->repository->findById($roleId);
		if (!$role) {
			$this->error('Role not found');
		}

		$form = $this->getForm('roleForm');
		$form->setDefaults($role->toArray());
		$this->handleShowRoleForm();
	}

	public function handleEditRule(int $ruleId)
	{
		$rule = $this->ruleRepository->findById($ruleId);
		if (!$rule) {
			$this->error('Rule not found');
		}
		$values = $rule->toArray();
		$form = $this->getForm('ruleForm');

		/** @var SelectInput */
		$select = $form->getComponent('resource');
		$select->setValue($values['resource']);
		$this->ruleFormRefresh($form, $values);
		$form->setValues($values);
		$this->handleShowRuleForm();
	}

	public function handleShowRoleForm()
	{
		$this->template->showRoleForm = true;
		$this->redrawControl('roleFormSnippet');
	}

	public function handleHideRoleForm()
	{
		$this->template->showRoleForm = false;
		$this->redrawControl('roleFormSnippet');
	}

	public function handleShowRuleForm()
	{
		$this->template->showRuleForm = true;
		$this->redrawControl('ruleFormSnippet');
	}

	public function handleHideRuleForm()
	{
		$this->template->showRuleForm = false;
		$this->redrawControl('ruleFormSnippet');
	}

	public function createComponentRoleForm($name)
	{
		$card = new Card($name);
		$card->addHeader()->addMinimize();
		$form = $card->addForm($name);
		$form->addHidden('id');
		$form->addText('name', 'Name')->setRequired();
		$form->addText('key', 'Key')->setRequired();
		$form->addSubmit('submit', 'Save');
		$form->addButton('Cancel', Html::el('a')->href($this->link('hideRoleForm!'))->class('ajax text-white')->setHtml('Cancel'))->setBtnClass('btn-danger');
		$form->onSuccess[] = [$this, 'roleFormSuccess'];
		return $card;
	}

	// public function createComponentRoleForm(): Form
	// {
	// 	$form = new BootstrapForm();
	// 	$form->renderMode = RenderMode::SIDE_BY_SIDE_MODE;
	// 	$form->addHidden('id');
	// 	$form->addText('name', 'Name')->setRequired();
	// 	$form->addText('key', 'Key')->setRequired();
	// 	$form->addSubmit('submit', 'Save');
	// 	$form->addButton('Cancel', Html::el('a')->href($this->link('hideRoleForm!'))->class('ajax text-white')->setHtml('Cancel'))->setBtnClass('btn-danger');
	// 	$form->onSuccess[] = [$this, 'roleFormSuccess'];

	// 	return $form;
	// }

	public function roleFormSuccess(Form $form, array $values): void
	{
		$roleId = (int) $values['id'];
		unset($values['id']);
		if ($roleId) {
			$role = $this->repository->findById($roleId);
		} else {
			$role = new Role();
		}
		$role->setValues($values);
		if ($this->repository->save($role)) {
			$this->flashMessage('Role saved.');
		}
	}

	public function createComponentRuleForm()
	{
		$form = new BootstrapForm();
		$form->renderMode = RenderMode::VERTICAL_MODE;
		$form->setAjax();
		$form->addHidden('id');
		$form->addHidden('role')->setValue((int) $this->getParameter('roleId'));
		$submit = $form->addSelect('resource', 'Resource', [0 => 'Select one ...'] + $this->resourceRepository->getIdNamePairs());
		$submit->setHtmlAttribute('onChange', "document.getElementById('refreshSubmit').click()");
		$form->addSubmit('refresh', 'Refresh')->setHtmlId('refreshSubmit')->setBtnClass('d-none');
		$form->addButton('cancel', Html::el('a')->href($this->link('hideRuleForm!'))->class('ajax text-white')->setHtml('Cancel'))->setBtnClass('btn-danger');
		$form->onSuccess[] = [$this, 'ruleFormRefresh'];
		$form->onSubmit[] = [$this, 'ruleFormSubmitted'];
		$form->onError[] = [$this, 'ruleFormError'];
		return $form;
	}

	public function ruleFormError()
	{
		$this->handleShowRuleForm();
	}

	public function ruleFormSubmitted(Form $form)
	{
		$hasErrors = $form->hasErrors();
		$errors = $form->getErrors();
	}

	public function ruleFormRefresh(Form $form, array $values)
	{
		$resourceId = $form->getComponent('resource')->getValue();
		if (!empty($resourceId)) {
			$resource = $this->resourceRepository->findById($resourceId);
			$privileges = $this->privilegeRepository->findByResource($resource)->getIdNamePairs();
			$form->addSelect('privilege', 'Privilege', $privileges);
			$form->addRadioList('type', 'Type', [Rule\Type::ALLOW => 'Allow', Rule\Type::DENY => 'Deny']);
			$form->removeComponent($form->getComponent('cancel'));
			$row = $form->addRow();
			$cell = $row->addCell(1);
			$cell->addSubmit('submit', 'Submit');
			$cell->addButton('cancel', Html::el('a')->href($this->link('hideRuleForm!'))->class('ajax text-white')->setHtml('Cancel'))->setBtnClass('btn-danger');
			// $form->addSubmit('submit', 'Submit');
			// $form->addButton('Cancel', Html::el('a')->href($this->link('hideRuleForm!'))->class('ajax text-white')->setHtml('Cancel'))->setBtnClass('btn-danger');
			$this->handleShowRuleForm();
		}

		if ($form->isSubmitted()) {
			if ($submitButton = $form->getComponent('submit')) {
				if ($submitButton->isSubmittedBy()) {
					$values = (array) $form->getValues();
					$this->ruleFormSuccess($form, $values);
				}
			}
		}
	}

	public function ruleFormSuccess(Form $form, array $values)
	{
		$rule = $this->ruleFactory->createFromValues($values);
		$this->ruleRepository->save($rule);
		$this->handleHideRuleForm();
		$this->redrawControl('rulesListSnippet'); // TODO: not working
	}
}
