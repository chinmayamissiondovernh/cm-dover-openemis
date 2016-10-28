<?php
/*
OpenEMIS School
Open School Management Information System

This program is free software: you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by the Free Software Foundation, 
either version 3 of the License, or any later version. This program is distributed in the hope 
that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should 
have received a copy of the GNU General Public License along with this program.  If not, see 
<http://www.gnu.org/licenses/>.  For more information please email contact@openemis.org.
*/

App::uses('AppModel', 'Model');

class EducationSubject extends AppModel {
	public $belongsTo = array(
		'ModifiedUser' => array(
			'className' => 'SecurityUser',
			'fields' => array('first_name', 'last_name'),
			'foreignKey' => 'modified_user_id',
			'type' => 'LEFT'
		),
		'CreatedUser' => array(
			'className' => 'SecurityUser',
			'fields' => array('first_name', 'last_name'),
			'foreignKey' => 'created_user_id',
			'type' => 'LEFT'
		)
	);
	public $hasMany = array('EducationGradesSubject');
	public $actsAs = array('ControllerAction', 'Reorder');

    public function __construct() {
        parent::__construct();

        $this->validate = array(
            'code' => array(
                'required' => array(
                    'rule' => 'notEmpty',
                    'required' => true,
                    'message' => $this->getErrorMessage('code')
                )
            ),
            'name' => array(
                'required' => array(
                    'rule' => 'notEmpty',
                    'required' => true,
                    'message' => $this->getErrorMessage('name')
                )
            )
        );
    }
	
	public function beforeAction() {
		parent::beforeAction();
		$this->Navigation->addCrumb($this->Message->getLabel($this->alias . '.title'));
		
		$this->fields['order']['visible'] = false;
		$this->fields['visible']['labelKey'] = 'general';
		$this->fields['visible']['type'] = 'select';
		$this->fields['visible']['default'] = 1;
		$this->fields['visible']['options'] = array(1 => __('Active'), 0 => __('Inactive'));
		
		$this->setVar('selectedPage', get_class($this));
		$this->setVar('header', $this->Message->getLabel($this->alias . '.title'));
	}

	public function index() {
		$this->recursive = 0;
		$data = $this->find('all', array('order' => array($this->alias . '.order')));
		$this->setVar('data', $data);
	}
	
	public function view($id=0) {
		parent::view($id);
		$this->render = 'view';
	}

	public function edit($id=0) {
		parent::edit($id);
		$this->render = 'edit';
	}
	
	public function add() {
		parent::add();
		$this->render = 'edit';
	}
	
	public function getSubjectList($type='name', $order='ASC') {
		$value = 'EducationSubject.' . $type;
		$orderField = 'EducationSubject.order';
		$result = $this->find('list', array(
			'fields' => array('EducationSubject.id', $value),
			'order' => array($orderField . ' ' . $order)
		));
		return $result;
	}
	
	public function getGradeSubjectId($subjectId, $classId){
		$data = $this->find('first', array(
			'conditions' => array(
				'EducationSubject.id' => $subjectId,
				'ClassGrade.class_id' => $classId
			),
			'joins' => array(
				array(
					'table' => 'education_grades_subjects',
					'alias' => 'EducationGradeSubject',
					'conditions' => array(
						'EducationGradeSubject.education_subject_id = EducationSubject.id' 
					)
				),
				array(
					'table' => 'class_grades',
					'alias' => 'ClassGrade',
					'conditions' => array(
						'EducationGradeSubject.education_grade_id = ClassGrade.education_grade_id'
					)
				)
			),
			'recursive' => -1,
			'fields' => array('EducationGradeSubject.id', 'EducationSubject.*')
		));
		
		return $data;
	}
}
?>
