<?php
/**
 * Copyright (c) 2013 TribeHR Corp - http://tribehr.com
 * Copyright (c) 2012 Luis E. S. Dias - www.smartbyte.com.br
 * 
 * Licensed under The MIT License. See LICENSE file for details.
 * Redistributions of files must retain the above copyright notice.
 */

class CustomReportsController extends CustomReportingAppController {
    
    public $uses = array('CustomReporting.CustomReport');
    public $helpers = array('Number', 'Form');

    public $path = null;
    
    public function index() {
	
        if (empty($this->request->data)) {
	
			// Get the lists of models and saved reports, and pass them to the view
			$models = $this->_getFilteredListOfModels();
			$customReports = $this->CustomReport->find('list');			
            $this->set(compact('models', 'customReports'));

        } else {
	
			if (isset($this->request->data['CustomReport']['model'])) {
				// TODO: validate the modelClass name - don't trust it
				$modelIndex = $this->data['CustomReport']['model'];
				$this->redirect(array('action' => 'wizard', $modelIndex));
			}
			
			// Submitted data we couldn't handle, so simply redirect to the index.
            $this->redirect(array('action'=>'index'));
        }
    }

    public function ajaxGetOneToManyOptions() {
        if ($this->request->is('ajax')) {
            Configure::write('debug',0);
            $this->autoRender = false;
            $this->layout = null;

            $modelClass = $this->request->data['model'];
            $this->loadModel($modelClass);
            $associatedModels = $this->{$modelClass}->getAssociated('hasMany');
            $associatedModels = array_combine($associatedModels, $associatedModels);

            $modelIgnoreList = Configure::read('ReportManager.modelIgnoreList');
            if ( isset($modelIgnoreList) && is_array($modelIgnoreList)) {
                foreach ($modelIgnoreList as $model) {
                    if (isset($associatedModels[$model]));
                        unset($associatedModels[$model]);
                }                
            }            
            
            $this->set('associatedModels',$associatedModels);
            $this->render('list_one_to_many_options');
        }
    }

    // calculate the html table columns width
    public function getTableColumnWidth($fieldsLength=array(),$fieldsType=array()) {
        $minWidth = 4;
        $maxWidth = 50;
        $tableColumnWidth = array();
        foreach ($fieldsLength as $field => $length): 
            if ( $length != '') {
                if ( $length < $maxWidth ) 
                    $width = $length * 9;
                else
                    $width = $maxWidth * 9;
                if ( $length < $minWidth ) 
                    $width = $length * 40;                
                $tableColumnWidth[$field] = $width;
            } else {
                $fieldType = $fieldsType[$field];
                switch ($fieldType) {
                    case "date":
                        $width = 120;
                        break;
                    case "float":
                        $width = 150;
                        break;                
                    default:
                        $width = 120;
                        break;
                }
                $tableColumnWidth[$field] = $width;
            }
        endforeach; 
        return $tableColumnWidth;
    }
    
    // calculate the html table width
    public function getTableWidth($tableColumnWidth = array()) {
        $tableWidth = array_sum($tableColumnWidth);
        return $tableWidth;
    }

    public function export2Xls(&$reportData = array(),&$fieldsList=array(), &$fieldsType=array(), &$oneToManyOption=null, &$oneToManyFieldsList=null, &$oneToManyFieldsType = null, &$showNoRelated = false ) {
        App::import('Vendor', 'ReportManager.Excel');
        $xls = new Excel();      
        $xls->buildXls($reportData,$fieldsList, $fieldsType, $oneToManyOption, $oneToManyFieldsList, $oneToManyFieldsType, $showNoRelated );
    }
 
    public function saveReport($modelClass = null,$oneToManyOption = null) {
        $content='<?php $reportFields=';
        $content.= var_export($this->data,1);
        $content.='; ?>'; 


        
        if ($this->data['Report']['ReportName'] != '') {
            $reportName = str_replace('.', '_', $this->data['Report']['ReportName']);
            $reportName = str_replace(' ', '_', $this->data['Report']['ReportName']);
        } else {
            $reportName = date('Ymd_His');
        }
        
        $oneToManyOption = ( $oneToManyOption == '' ? $oneToManyOption : $oneToManyOption . '.' );
        $fileName = $modelClass . '.' . $oneToManyOption . $reportName.".crp";
        $file = new File(APP.$this->path.$fileName, true, 777);
        $file->write($content,'w',true);
        $file->close();
    }

    public function loadReport($fileName) {
        require(APP.$this->path.$fileName);
        $this->data = $reportFields;
        $this->set($this->data);
    }

    public function deleteReport($fileName) {
        if ($this->request->is('ajax')) {
            Configure::write('debug',0);
            $this->autoRender = false;
            $this->layout = null;
            
            $fileName = APP.$this->path.$fileName;
            $file = new File($fileName, false, 777);
            $file->delete();
            $this->set('files',$this->listReports());
            $this->render('list_reports');
        }
    }

    public function wizard($modelClass = null) {
		if (is_null($modelClass)) {
            $this->Session->setFlash(__('Please select a model or a saved report'));
            $this->redirect(array('action'=>'index'));			
		}

        if (empty($this->request->data)) {
			// Let's get the list of fields to make available to the report
			$modelSchema = $this->_getCompleteFieldList($modelClass);

            $this->set('modelClass',$modelClass);
            $this->set('modelSchema',$modelSchema);

        } else {
			// Let's get the list of fields to make available to the report
			$modelSchema = $this->_getCompleteFieldList($modelClass);

            
            $fieldsList = array();
            $fieldsPosition = array();
            $fieldsType = array();
            $fieldsLength = array();
            
            $conditions = array();
            $conditionsList = array();
            
			$containList = array();
			
            $oneToManyFieldsList  = array();
            $oneToManyFieldsPosition  = array();
            $oneToManyFieldsType  = array();
            $oneToManyFieldsLength = array();
            
            foreach ($this->request->data  as $model => $fields) {
                if ( is_array($fields) ) {
                    foreach ($fields  as $field => $parameters) {
                        if ( is_array($parameters) ) {                    
                            if ( isset($modelSchema[$model]) ) {
                                if ( $parameters['Add'] ) {
									// If we haven't previously added it to the contain, then add it
									if ($model != $modelClass && !in_array($model, $containList)) {
										$containList[] = $model;
									}
                                    $fieldsPosition[$model.'.'.$field] = ( $parameters['Position'] != '' ? $parameters['Position'] : 0 );
                                    $fieldsType[$model.'.'.$field] = $parameters['Type'];
                                    $fieldsLength[$model.'.'.$field] = $parameters['Length'];
                                }
                                $criteria = '';                                    
                                if ($parameters['Example'] != '' && $parameters['Filter']!='null' ) {
                                    if ( $parameters['Not'] ) {
                                        switch ($parameters['Filter']) {
                                            case '=':
                                                $criteria .= ' !'.$parameters['Filter'];
                                                break;
                                            case 'LIKE':
                                                $criteria .= ' NOT '.$parameters['Filter'];
                                                break;
                                            case '>':
                                                $criteria .= ' <=';
                                                break;
                                            case '<':
                                                $criteria .= ' >=';
                                                break;
                                            case '>=':
                                                $criteria .= ' <';
                                                break;
                                            case '<=':
                                                $criteria .= ' >';
                                                break;
                                            case 'null':
                                                $criteria = ' !=';
                                                break;
                                        }
                                    } else {
                                        if ($parameters['Filter']!='=') 
                                            $criteria .= ' '.$parameters['Filter'];
                                    }

                                    if ($parameters['Filter']=='LIKE')
                                        //$example = '%'. mysql_real_escape_string($parameters['Example']) . '%';
                                        $example = '%'.$parameters['Example'] . '%';
                                    else
                                        //$example = mysql_real_escape_string($parameters['Example']);
                                        $example = $parameters['Example'];

                                    $conditionsList[$model.'.'.$field.$criteria] = $example;
                                }
                                if ( $parameters['Filter']=='null' ) {
                                    $conditionsList[$model.'.'.$field.$criteria] = null;                                        
                                }
                            }
/*                            // One to many reports
                            if ( $oneToManyOption != '') {
                                if ( isset($parameters['Add']) && $model == $oneToManyOption ) {
                                    $oneToManyFieldsPosition[$model.'.'.$field] = ( $parameters['Position']!='' ? $parameters['Position'] : 0 );
                                    $oneToManyFieldsType[$model.'.'.$field] = $parameters['Type'];
                                    $oneToManyFieldsLength[$model.'.'.$field] = $parameters['Length'];
                                }                                    
                            } */

                        } // is array parameters
                    } // foreach field => parameters
                    if (count($conditionsList)>0) {
                        $conditions[$this->data['CustomReport']['Logical']] = $conditionsList;
                    }
                } // is array fields
            } // foreach model => fields
            asort($fieldsPosition);
            $fieldsList = array_keys($fieldsPosition);
            $order = array();
            if ( isset($this->data['CustomReport']['OrderBy1']) )
                $order[] = $this->data['CustomReport']['OrderBy1'] . ' ' . $this->data['CustomReport']['OrderDirection'];
            if ( isset($this->data['CustomReport']['OrderBy2']) )
                $order[] = $this->data['CustomReport']['OrderBy2'] . ' ' . $this->data['CustomReport']['OrderDirection'];
            
            $tableColumnWidth = $this->getTableColumnWidth($fieldsLength,$fieldsType);
            $tableWidth = $this->getTableWidth($tableColumnWidth);
			$recursive = 1;

            $reportData = $this->{$modelClass}->find('all',array(
                'recursive' => $recursive,
                'fields' => $fieldsList,
                'order' => $order,
                'conditions' => $conditions,
				'contain' => $containList,
            ));

            $this->layout = 'report';
                        
            $this->set('tableColumnWidth',$tableColumnWidth);
            $this->set('tableWidth',$tableWidth);
            
            $this->set('fieldList',$fieldsList);
            $this->set('fieldsType',$fieldsType);
            $this->set('fieldsLength',$fieldsLength);
            $this->set('reportData',$reportData);
            $this->set('reportName',$this->data['CustomReport']['Title']);
            $this->set('reportStyle',$this->data['CustomReport']['Style']);
            $this->set('showRecordCounter',$this->data['CustomReport']['ShowRecordCounter']);

            if ( $this->data['CustomReport']['Output'] == 'html') {
                $this->render('report_display');
            } else { // Excel file
                $this->layout = null;
                $this->export2Xls(
                        $reportData, 
                        $fieldsList, 
                        $fieldsType, 
                        $oneToManyOption, 
                        $oneToManyFieldsList, 
                        $oneToManyFieldsType, 
                        $showNoRelated );
            }

//           if ($this->data['Report']['SaveReport'])
//                $this->saveReport($modelClass);
        }
    }

	/**
	 * Get a list of all the Models we can report on, properly
	 * respecting the Whitelist and Blacklist configurations
	 * set in the bootstrap file. Only include the top-level models
	 * not the associated models.
	 *
	 * @return array Listing of Model Names
	 */
	function _getFilteredListOfModels() {
		
		// If we have a whitelist then we will use that. If there is no whitelist,
		// then we will start with the complete list of models in the application.
		if (Configure::read('CustomReporting.modelWhitelist') == false) {
			$models = App::objects('Model');
		} else {
			$models = Configure::read('CustomReporting.modelWhitelist');
			
			// Note, some of the whitelist entries might not be string values,
			// but instead array values of whitelisted associated models. In these
			// cases, the actual model name is the *index* not the *value*. Let's
			// get rid of the 2nd level arrays, and simply include the model name.
			foreach ($models as $index => $value) {
				if (!is_numeric($index)) {
					unset($models[$index]);
					$models[] = $index;
				}
			}
		}
		
		// Now remove any models from the list that also exist on the blacklist
		$modelBlacklist = Configure::read('CustomReporting.modelBlacklist');
        if ($modelBlacklist !== false) {
            foreach ($models as $index => $model) {
				if (in_array($model, $modelBlacklist)) {
					unset($models[$index]);
				}
            }                
        }

		// Let's alphabetize the list for consistency, then return
		// an array with the indexes and the values the model names.
		// TODO: Replace the values with more human-friendly values
		sort($models);
		
		return array_combine($models, $models);
	}
	
	/**
	 * Get a complete list of all the fields that we can report on
	 * or filter by.
	 * 
	 * @return array Listing of all fields that are available
	 *               array (
	 *                 'PrimaryModel' => array(
	 *                     'field1' => array (schema info...),
	 *                     'field2' => array (schema info...),
	 *                      ...
	 *                  ),
	 *                 'AssociatedModel1' => array(
	 *                     'field1' => array (schema info...),
	 *                     'field2' => array (schema info...),
	 *                      ...
	 *                  )
	 *                 'AssociatedModel2' => array(
	 *                     'field1' => array (schema info...),
	 *                     'field2' => array (schema info...),
	 *                      ...
	 *                  )
	 *               )
	 */	
	function _getCompleteFieldList($baseModelClass) {

        $modelWhitelist = Configure::read('CustomReporting.modelWhitelist');
        $modelBlacklist = Configure::read('CustomReporting.modelBlacklist');
		
		// Start with the base model
		$completeSchema = array($baseModelClass => $this->_getFilteredListOfModelFields($baseModelClass));

		// Add any associated models.
		$associatedModels = $this->{$baseModelClass}->getAssociated();		
        foreach ($associatedModels as $key => $value) {

			// Compare these models to the list of allowed models in
			// the whitelists and blacklists.
			if (is_array($modelBlacklist) && in_array($key, $modelBlacklist)) {
				// It's on the blacklist. Destroy it.
				unset($associatedModels[$key]);
			} elseif (isset($modelWhitelist[$baseModelClass]) && is_array($modelWhitelist[$baseModelClass]) && !in_array($key, $modelWhitelist[$baseModelClass])) {
				// There is a whitelist, and it's not on it. Destroy it.
				unset($associatedModels[$key]);
			} else {
	            $associatedModelClassName = $this->{$baseModelClass}->{$value}[$key]['className'];
	    		$completeSchema[$key] = $this->_getFilteredListOfModelFields($associatedModelClassName);				
			}
		}
				
		return $completeSchema;
	}
	
	/**
	 * Get a list of the fields we can report on for this model, properly
	 * respecting the global and model-specific blacklists defined in the
	 * configuration.
	 */
	function _getFilteredListOfModelFields($modelClass) {
		
        $displayForeignKeys = Configure::read('CustomReporting.displayForeignKeys');
        $globalFieldBlacklist = Configure::read('CustomReporting.globalFieldBlacklist');
        $modelFieldBlacklist = Configure::read('CustomReporting.modelFieldBlacklist');
		

        $this->loadModel($modelClass);
        $modelSchema = $this->{$modelClass}->schema();

        
        if (is_array($globalFieldBlacklist)) {
            foreach ($globalFieldBlacklist as $field) {
                unset($modelSchema[$field]);
            }                
        }

		if (isset($modelFieldBlacklist[$modelClass])) {
            foreach ($modelFieldBlacklist[$modelClass] as $field) {
                unset($modelSchema[$field]);
            }                			
		}
        
        if (isset($displayForeignKeys) && $displayForeignKeys == false) { 
            foreach($modelSchema as $field => $value) {
                if ( substr($field,-3) == '_id' ) {
                    unset($modelSchema[$field]);
				}
            }
        }
		return $modelSchema;
	}
}