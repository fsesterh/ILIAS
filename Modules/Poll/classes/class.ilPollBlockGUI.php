<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Block/classes/class.ilBlockGUI.php");
include_once("./Modules/Poll/classes/class.ilObjPoll.php");

/**
* BlockGUI class for polls. 
*
* @author Jörg Lützenkirchen
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPollBlockGUI: ilColumnGUI
* @ingroup ModulesPoll
*/
class ilPollBlockGUI extends ilBlockGUI
{
	static $block_type = "poll";
	
	protected $poll_block; // [ilPollBlock]
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $lng;
		
		parent::__construct();
			
		$lng->loadLanguageModule("poll");		
		$this->setRowTemplate("tpl.block.html", "Modules/Poll");
	}
		
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return true;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{		
		return IL_SCREEN_SIDE;		
	}

	/**
	* Do most of the initialisation.
	*/
	function setBlock($a_block)
	{
		$this->setBlockId($a_block->getId());
		$this->poll_block = $a_block;				
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}
	
	function fillRow($a_poll)
	{		
		global $ilCtrl, $lng, $ilUser;
		
		
		// handle messages
		
		$mess = $this->poll_block->getMessage($ilUser->getId());
		if($mess)
		{
			$this->tpl->setVariable("TXT_QUESTION", $mess);
			return;
		}		
		
		
		// nested form problem
		if(!$_SESSION["il_cont_admin_panel"])
		{
			// vote
			
			if($this->poll_block->mayVote($ilUser->getId()))
			{						
				if($this->poll_block->getPoll()->getNonAnonymous())
				{
					$this->tpl->setCurrentBlock("non_anon_bl");				
					$this->tpl->setVariable("NON_ANONYMOUS", $lng->txt("poll_non_anonymous_warning"));
					$this->tpl->parseCurrentBlock();
				}

				$is_multi_answer = ($this->poll_block->getPoll()->getMaxNumberOfAnswers() > 1);

				if(isset($_SESSION["last_poll_vote"][$this->poll_block->getPoll()->getId()]))
				{
					$last_vote = $_SESSION["last_poll_vote"][$this->poll_block->getPoll()->getId()];				
					unset($_SESSION["last_poll_vote"][$this->poll_block->getPoll()->getId()]);

					if($is_multi_answer)
					{
						$error = sprintf($lng->txt("poll_vote_error_multi"),
							$this->poll_block->getPoll()->getMaxNumberOfAnswers());						
					}
					else
					{
						$error = $lng->txt("poll_vote_error_single");
					}

					$this->tpl->setCurrentBlock("error_bl");				
					$this->tpl->setVariable("FORM_ERROR", $error);
					$this->tpl->parseCurrentBlock();
				}		
				
				$this->tpl->setCurrentBlock("answer");
				foreach($a_poll->getAnswers() as $item)
				{								
					if(!$is_multi_answer)
					{
						$this->tpl->setVariable("ANSWER_INPUT", "radio");
						$this->tpl->setVariable("ANSWER_NAME", "aw"); 
					}
					else
					{
						$this->tpl->setVariable("ANSWER_INPUT", "checkbox");
						$this->tpl->setVariable("ANSWER_NAME", "aw[]"); 
						
						if(is_array($last_vote) && in_array($item["id"], $last_vote))
						{
							$this->tpl->setVariable("ANSWER_STATUS", 'checked="checked"'); 
						}
					}						
					$this->tpl->setVariable("VALUE_ANSWER", $item["id"]);
					$this->tpl->setVariable("TXT_ANSWER_VOTE", nl2br($item["answer"]));
					$this->tpl->parseCurrentBlock();
				}		

				$ilCtrl->setParameterByClass("ilobjpollgui",
						"ref_id", $this->getRefId());		
				$url = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
							"vote");
				$ilCtrl->clearParametersByClass("ilobjpollgui");
				
				$url .= "#poll".$a_poll->getID();

				$this->tpl->setVariable("URL_FORM", $url);
				$this->tpl->setVariable("CMD_FORM", "vote");
				$this->tpl->setVariable("TXT_SUBMIT", $lng->txt("poll_vote"));		
								
				if($this->poll_block->getPoll()->getVotingPeriod())
				{
					$this->tpl->setVariable("TXT_VOTING_PERIOD",
						sprintf($lng->txt("poll_voting_period_info"),
							ilDatePresentation::formatDate(new ilDateTime($this->poll_block->getPoll()->getVotingPeriodEnd(), IL_CAL_UNIX))));
				}
			}


			// result		
			if ($this->poll_block->maySeeResults($ilUser->getId()))
			{
				if (!$this->poll_block->mayNotResultsYet($ilUser->getId()))
				{
					$answers = array();
					foreach ($a_poll->getAnswers() as $item)
					{
						$answers[$item["id"]] = $item["answer"];
					}

					$perc = $this->poll_block->getPoll()->getVotePercentages();
					$total = $perc["total"];
					$perc = $perc["perc"];

					$this->tpl->setVariable("TOTAL_ANSWERS", sprintf($lng->txt("poll_population"), $total));
					
					// sort results by votes / original position
					if ($this->poll_block->getPoll()->getSortResultByVotes())
					{
						$order = array_keys(ilUtil::sortArray($perc, "abs", "desc", true, true));

						foreach (array_keys($answers) as $answer_id)
						{
							if (!in_array($answer_id, $order))
							{
								$order[] = $answer_id;
							}
						}
					} 
					else
					{
						$order = array_keys($answers);
					}

					// pie chart
					if ($this->poll_block->showResultsAs() == ilObjPoll::SHOW_RESULTS_AS_PIECHART)
					{

						include_once("./Services/Chart/classes/class.ilChart.php");

						$chart = ilChart::getInstanceByType(ilCHart::TYPE_PIE, "poll_results_pie_". $this->getRefId());
						$chart->setSize("100%", 200); 
						$chart->setAutoResize(true);

						$chart_data = $chart->getDataInstance();

						foreach ($order as $answer_id)
						{							
							$chart_data->addPoint(
								round($perc[$answer_id]["perc"]), 
								nl2br($answers[$answer_id])
							);
						}

						// disable legend, use inner labels - currently not preferred
						// $chart_data->setLabelRadius(0.8);
						
						$chart->addData($chart_data);

						$this->tpl->setVariable("PIE_CHART", $chart->getHTML());
					}
					// bar chart
					else
					{						
						$this->tpl->setCurrentBlock("answer_result");
						foreach ($order as $answer_id)
						{
							$this->tpl->setVariable("TXT_ANSWER_RESULT", nl2br($answers[$answer_id]));
							$this->tpl->setVariable("PERC_ANSWER_RESULT", round($perc[$answer_id]["perc"]));
							$this->tpl->parseCurrentBlock();
						}
					}
				}
				else 
				{							
					$rel =  ilDatePresentation::useRelativeDates();
					ilDatePresentation::setUseRelativeDates(false);
					$end = $this->poll_block->getPoll()->getVotingPeriodEnd();
					$end = ilDatePresentation::formatDate(new ilDateTime($end, IL_CAL_UNIX));
					ilDatePresentation::setUseRelativeDates($rel);
					
					$this->tpl->setVariable("TOTAL_ANSWERS", $lng->txt("poll_block_message_already_voted").
						' '.sprintf($lng->txt("poll_block_results_available_on"), $end));					
				}
			}
			else if($this->poll_block->getPoll()->hasUserVoted($ilUser->getId()))
			{
				$this->tpl->setVariable("TOTAL_ANSWERS", $lng->txt("poll_block_message_already_voted"));
			}
		}
		
				
		$this->tpl->setVariable("ANCHOR_ID", $a_poll->getID());
		$this->tpl->setVariable("TXT_QUESTION", nl2br(trim($a_poll->getQuestion())));
		
		$desc = trim($a_poll->getDescription());
		if($desc)
		{
			$this->tpl->setVariable("TXT_DESC", nl2br($desc));
		}

		$img = $a_poll->getImageFullPath();
		if($img)
		{
			$this->tpl->setVariable("URL_IMAGE", $img);
		}

		if ($this->poll_block->showComments()) {
			$this->tpl->setCurrentBlock("comment_link");
			$this->tpl->setVariable("LANG_COMMENTS", $lng->txt('poll_comments'));
			$this->tpl->setVariable("COMMENT_JSCALL", $this->commentJSCall());
		}

	}

	/**
	* Get block HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilAccess, $ilUser;
		
		$this->poll_block->setRefId($this->getRefId());		
		$this->may_write = $ilAccess->checkAccess("write", "", $this->getRefId());
		$this->has_content = $this->poll_block->hasAnyContent($ilUser->getId(), $this->getRefId());
		
		if(!$this->may_write && !$this->has_content)
		{
			return "";
		}
		
		$poll_obj = $this->poll_block->getPoll();
		$this->setTitle($poll_obj->getTitle());
		$this->setData(array($poll_obj));	
		
		$ilCtrl->setParameterByClass("ilobjpollgui",
			"ref_id", $this->getRefId());
				
		if(!$this->poll_block->getMessage($ilUser->getId()))
		{
			// notification
			include_once "./Services/Notification/classes/class.ilNotification.php";
			if(ilNotification::hasNotification(ilNotification::TYPE_POLL, $ilUser->getId(), $this->poll_block->getPoll()->getId()))
			{						
				$this->addBlockCommand(
					$ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
						"unsubscribe"),
					$lng->txt("poll_notification_unsubscribe"));
			}
			else
			{
				$this->addBlockCommand(
					$ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
						"subscribe"),
					$lng->txt("poll_notification_subscribe"));
			}
		}
	
		if ($this->may_write)
		{
			// edit				
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
					"render"),
				$lng->txt("edit_content"));
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
					"edit"),
				$lng->txt("settings"));
			
			/* delete (#10993 - see ilBlockGUI)
			$parent_id = $tree->getParentId($this->getRefId());			
			$type = ilObject::_lookupType($parent_id, true);
			$class = $objDefinition->getClassName($type);
			if($class)
			{
				$class = "ilobj".strtolower($class)."gui";
				$ilCtrl->setParameterByClass($class, "ref_id", $parent_id);		
				$ilCtrl->setParameterByClass($class, "item_ref_id", $this->getRefId());	
				$this->addBlockCommand(
					$ilCtrl->getLinkTargetByClass($class, "delete"),
					$lng->txt("delete"));	
			}			 
			*/						
		}
		
		$ilCtrl->clearParametersByClass("ilobjpollgui");
		
		return parent::getHTML();
	}

	/**
	 * Builds JavaScript Call to open CommentLayer via html link
	 *
	 * @return string jsCall
	 */
	private function commentJSCall()
	{
		include_once("./Services/Notes/classes/class.ilNoteGUI.php");
		include_once("./Services/Object/classes/class.ilCommonActionDispatcherGUI.php");

		$refId = $this->getRefId();
		$objectId = ilObject2::_lookupObjectId($refId);

		$ajaxHash = ilCommonActionDispatcherGUI::buildAjaxHash(
			ilCommonActionDispatcherGUI::TYPE_REPOSITORY, $refId, "poll", $objectId);


		$comment = new ilNoteGUI();
		$jsCall = $comment->getListCommentsJSCall($ajaxHash);

		return $jsCall;
	}

}

?>