<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Contributte\FormsBootstrap\Inputs\SelectInput;
use Models\Entities\Role\Role;
use Models\Entities\Rule;
use Models\Entities\Rule\RuleFactory;
use Models\Entities\Rule\Type;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Repositories\PrivilegeRepository;
use Repositories\ResourceRepository;
use Repositories\RoleRepository;
use Repositories\RuleRepository;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Utils\DataGrid\Action;
use Utils\DataGrid\Column;
use Utils\DataGrid\ToolbarButton;

final class RolesPresenter extends DefaultPresenter
{
	private RoleRepository $roleRepository;
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
		$this->roleRepository = $roleRepository;
		$this->resourceRepository = $resourceRepository;
		$this->privilegeRepository = $privilegeRepository;
		$this->ruleRepository = $ruleRepository;
		$this->ruleFactory = $ruleFactory;
	}

	public function renderDefault(): void
	{
		$roles = $this->roleRepository->findAll();
		$this->template->roles = $roles;
	}

	public function renderEdit(int $id)
	{
		$role = $this->roleRepository->findById($id);
		if (!$role) {
			$this->error('Role not found');
		}
		$this->template->role = $role;
	}

	public function createComponentRolesGrid()
	{
		$this->addGrid('rolesGrid', $this->roleRepository->getDataSource())
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::TEXT, 'name', 'Name')
			->addColumn(Column::TEXT, 'key', 'Key')
			->addAction(Action::VIEW, ':edit', null, false)
			->addAction(Action::EDIT, 'editRole!')
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete role %s?', 'name'), 'deleteRole!')
			->addToolbarButton(ToolbarButton::ADD, 'Add new role', 'showRoleForm!');
	}

	public function createComponentRulesGrid()
	{
		$roles = $this->roleRepository->getIdNamePairs();
		$resources = $this->resourceRepository->getIdNamePairs();
		$privileges = $this->privilegeRepository->findAll()->getIdNamePairs();
		$this->addGrid('rulesGrid', $this->ruleRepository->getDataSource(['role_id' => $this->getParameter('id')]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::TEXT_MULTISELECT, 'role_id', 'Role', $roles)
			->addColumn(Column::TEXT_MULTISELECT, 'resource_id', 'Resource', $resources)
			->addColumn(Column::TEXT_MULTISELECT, 'type', 'Type', [Type::ALLOW => 'allow', Type::DENY => 'deny'])
			->addColumn(Column::TEXT, 'privilege_id', 'Privilege', $privileges)
			->addAction(Action::EDIT, 'editRule!', ['ruleId' => 'id'])
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete rule #%s?', 'id'), 'deleteRule!', ['ruleId' => 'id'])
			->addToolbarButton(ToolbarButton::ADD, 'Add new rule', 'showRuleForm!');
	}

	public function handleEditRole(int $id)
	{
		$role = $this->roleRepository->findById($id);
		if (!$role) {
			$this->error('Role not found');
		}

		$form = $this->getForm('roleForm');
		$form->setDefaults($role->toArray());
		$this->handleShowRoleForm();
		$this->template->roleEdit = true;
	}

	public function handleDeleteRole(int $id)
	{
		$role = $this->roleRepository->findById($id);
		if (!$role) {
			$this->flashMessage('Role not found!', 'error');
			return;
		}
		if ($this->roleRepository->delete($role)) {
			$this->flashMessage('Role id ' . $id . ' deleted', 'success');
			$this->getGrid('rolesGrid')->reload();
		}
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
		$this->template->ruleEdit = true;
	}

	public function handleDeleteRule(int $ruleId)
	{
		$rule = $this->ruleRepository->findById($ruleId);
		if (!$rule) {
			$this->flashMessage('Rule not found!', 'error');
			return;
		}
		if ($this->ruleRepository->delete($rule)) {
			$this->flashMessage('Rule id ' . $ruleId . ' deleted', 'success');
			$this->getGrid('rulesGrid')->reload();
		}
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

	public function createComponentRoleForm(): Form
	{
		$form = new BootstrapForm();
		$form->renderMode = RenderMode::SIDE_BY_SIDE_MODE;
		$form->addHidden('id');
		$form->addText('name', 'Name')->setRequired();
		$form->addText('key', 'Key')->setRequired();
		$form->addSubmit('submit', 'Save');
		$form->addButton('Cancel', Html::el('a')->href($this->link('hideRoleForm!'))->class('ajax text-white')->setHtml('Cancel'))->setBtnClass('btn-danger');
		$form->onSuccess[] = [$this, 'roleFormSuccess'];

		return $form;
	}

	public function roleFormSuccess(Form $form, array $values): void
	{
		$roleId = (int) $values['id'];
		unset($values['id']);
		if ($roleId) {
			$role = $this->roleRepository->findById($roleId);
		} else {
			$role = new Role();
		}
		$role->setValues($values);
		if ($this->roleRepository->save($role)) {
			$this->flashMessage('Role saved.');
		}
	}

	public function createComponentRuleForm()
	{
		$form = new BootstrapForm();
		$form->renderMode = RenderMode::SIDE_BY_SIDE_MODE;
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
			$form->addSubmit('submit', 'Submit');
			$form->addButton('Cancel', Html::el('a')->href($this->link('hideRuleForm!'))->class('ajax text-white')->setHtml('Cancel'))->setBtnClass('btn-danger');
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