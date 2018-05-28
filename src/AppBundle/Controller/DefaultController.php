<?php

namespace AppBundle\Controller;

use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Request;
use Zend\Paginator\Paginator;

class DefaultController extends FrontendController
{
    public function defaultAction(Request $request)
    {
	    // $this->view->blogpostslist = new DataObject\Blogpost\Listing();

	    /*
	    $paginator = Paginator::factory($list);
	    $paginator->setCurrentPageNumber( $request->get('page') );
	    $paginator->setItemCountPerPage(2);
	    $this->view->paginator  = $paginator;
	    */
    }

    public function blogarticleAction(Request $request)
    {
    	$blogarticle = DataObject\Blogpost::getById($request->get('id'));
    	var_dump($blogarticle->getId());exit;
    }

    public function eveningEventsAction($evening)
    {
	    $eventsData = $this->getEveningEvents($evening);

	    return $this->render(':Default:gameEventDisplay.html.twig', array(
		    'neuner'    => $eventsData['neuner'],
		    'kraenze'   => $eventsData['kraenze'],
		    'banden'    => $eventsData['banden']
	    ));
    }

    public function homeAction(Request $request)
    {
	    $evenings = new DataObject\NinepinsEvening\Listing();
	    $evenings->setCondition("date >= ?", time());
	    $evenings->setLimit(1);
	    $evenings->load();

	    $nextEvening = $evenings->getObjects();

	    $members = new DataObject\User\Listing();
	    $members->setOrderKey("RAND()", false);
	    $members->setLimit(8);
	    $members->load();

	    $games = new DataObject\Game\Listing();
	    $games->setOrderKey("RAND()", false);
	    $games->setLimit(6);
	    $games->load();

	    return array(
	    	'nextEvening'   => (isset($nextEvening[0]) ? $nextEvening[0] : false),
		    'members'       => $members,
		    'games'         => $games,
		    'monthnames'    => $this->getMonthNames(),
		    'daynames'      => $this->getDayNames()
	    );
    }

    public function userDataAction(Request $request)
    {

    }

    public function historyAction(Request $request)
    {

    }

    public function membersAction(Request $request)
    {
	    $members = new DataObject\User\Listing();
	    $members->setOrderKey('memberSince');
	    $members->setOrder('asc');
	    $members->load();

	    return array(
	    	'members' => $members
	    );
    }

	public function memberRecordsAction($member)
	{
		$eveningUsers = new DataObject\EveningUser\Listing();
		$eveningUsers->setCondition("user__id = :user", array('user' => $member->getId()));
		$eveningUsers->load();

		$countBahnmeister = 0;
		$countSonnenkoenig = 0;
		$yearValue = 0;
		$yearAverage = 0;
		$counter = 0;
		foreach($eveningUsers as $evening) {
			if ($evening->getIsBahnmeister())
				$countBahnmeister++;
			elseif ($evening->getIsSonnenkoenig())
				$countSonnenkoenig++;
			$yearValue += $evening->getBahnmeisterResult();
			$counter++;
		}
		$yearAverage = $yearValue/$counter;

		$eveningUser = new DataObject\EveningUser\Listing();
		/*
		$eveningUser->setCondition("user__id = :user AND isBahnmeister = :isBahnmeister", array(
			'user'          => $member->getId(),
			'isBahnmeister' => true
		));
		*/
		$eveningUser->setCondition("user__id = :user", array(
			'user'          => $member->getId()
		));
		$eveningUser->setOrderKey('bahnmeisterResult');
		$eveningUser->setOrder('desc');
		$eveningUser->setLimit(1);
		$eveningUser->load();
		$objects = $eveningUser->getObjects();

		$memberEvents = $this->getMemberEvents($member);

		return $this->render(':Default:memberRecords.html.twig', array(
			'eveningUser'       => (isset($objects[0]) ? $objects[0] : false),
			'countBahnmeister'  => $countBahnmeister,
			'yearAverage'       => $yearAverage,
			'countSonnenkoenig' => $countSonnenkoenig,
			'neuner'            => $memberEvents['neuner'],
			'kraenze'           => $memberEvents['kraenze'],
			'banden'            => $memberEvents['banden'],
			'countOn'           => $counter
		));
	}

	private function getMemberEvents($member)
	{
		// get all events
		$events = new DataObject\GameEvent\Listing();
		$events->setCondition("user__id = :member", array(
			'member' => $member->getId()
		));

		$neuner = array();
		$kraenze = array();
		$banden = array();

		foreach($events->getObjects() as $event) {
			switch($event->getEtype()) {
				case(1):
					array_push($neuner, $event);
					break;
				case(2):
					array_push($kraenze, $event);
					break;
				case(3):
					array_push($banden, $event);
					break;
				default:
					break;
			}
		}

		return array(
			'neuner'    => $neuner,
			'kraenze'   => $kraenze,
			'banden'    => $banden
		);
	}

	public function eveningsAction(Request $request)
    {
	    $year = $request->get('year', date('Y'));


	    $eveningClassId = DataObject\NinepinsEvening::classId();
	    $eveningUserClassId = DataObject\EveningUser::classId();

	    $eveningUsers = new DataObject\EveningUser\Listing();
	    $eveningUsers->setCondition("
			oo_id IN (
				SELECT DISTINCT oreu.src_id 
				FROM object_relations_$eveningUserClassId oreu
				LEFT JOIN object_query_$eveningClassId oqe ON oreu.dest_id = oqe.oo_id 
				WHERE DATE_FORMAT(FROM_UNIXTIME(oqe.date), '%Y') = $year)	        
	    ");
	    //$eveningUsers->setGroupBy('evening__id');
	    //$eveningUsers->setOrderKey("oo_id FROM object_query_$eveningClassId", false);
	    //$eveningUsers->setOrder('asc');
	    //$eveningUsers->setCondition("DATE_FORMAT(FROM_UNIXTIME(date), '%Y') = ?", $year);

	    //$eveningUsers->load();

	    $results = array();
	    $monthNames = $this->getMonthNames();
	    $dayNames = $this->getDayNames();
	    $overalls = array();

	    foreach ($eveningUsers as $eveningUser) {
	    	$evening = $eveningUser->getEvening();
	    	$eveningMonth = $evening->getDate()->format('n');
	    	$eveningId = $evening->getId();

	    	if (!isset($results[$eveningMonth][$eveningId])) {
			    $overalls['evenings']++;
			    $dayName = $dayNames[$evening->getDate()->format('w')].', '.$evening->getDate()->format("d.m.Y");
	    	    $results[$eveningMonth][$eveningId]['name'] = $dayName;
	    	    $results[$eveningMonth][$eveningId]['bahnmeister'] = $evening->getBahnmeister()->getShort();
		        $results[$eveningMonth][$eveningId]['sonnenkoenig'] = $evening->getSonnenkoenig()->getShort();
		    }

		    $results[$eveningMonth][$eveningId]['players']++;
		    $results[$eveningMonth][$eveningId]['neuner'] += $eveningUser->getNeuner();
		    $results[$eveningMonth][$eveningId]['kraenze'] += $eveningUser->getKraenze();
		    $results[$eveningMonth][$eveningId]['banden'] += $eveningUser->getPudel();

		    $overalls['players']++;
		    $overalls['neuner'] += $eveningUser->getNeuner();
		    $overalls['kraenze'] += $eveningUser->getKraenze();
		    $overalls['banden'] += $eveningUser->getPudel();
	    }

	    $overalls['averagePlayers'] = $overalls['players']/$overalls['evenings'];
	    $overalls['averageNeuner'] = $overalls['neuner']/$overalls['evenings'];
	    $overalls['averageKraenze'] = $overalls['kraenze']/$overalls['evenings'];
	    $overalls['averageBanden'] = $overalls['banden']/$overalls['evenings'];

	    /*
	    $eveningClassId = DataObject\NinepinsEvening::classId();

	    $evenings = new DataObject\NinepinsEvening\Listing();
	    $evenings->setCondition("DATE_FORMAT(FROM_UNIXTIME(date), '%Y') = ?", $year);
	    $evenings->setCondition("
	        o_id IN (SELECT src_id FROM object_relation_$eveningClassId where fieldname = 'users')
        ");
	    $evenings->setOrderKey('date');
	    $evenings->setOrder('asc');

	    $evenings->load();

	    /*
	    $eveningClassId = DataObject\NinepinsEvening::classId();
	    $userEveningClassId = DataObject\EveningUser::classId();

	    $combinedListing = new DataObject\Listing();
	    $combinedListing->setCondition("
	        o_className IN ('NinepinsEvening', 'EveningUser')
	    ");

	    foreach($combinedListing as $item) {
	    	echo get_class($item) . "<br/>";
	    }

	    exit;
	    */

	    /*
	    $eveningClassId = DataObject\NinepinsEvening::classId();
	    $userEveningClassId = DataObject\EveningUser::classId();

	    $db = \Pimcore\Db::get();
	    $fieldsArray = $db->fetchAll("
	    	SELECT oqu.bahnmeister__id, oqeu.user__id
				FROM object_query_".$classId." oqu
	            LEFT JOIN object_relations_".$classId." ore	ON oqu.oo_id = ore.src_id
	            LEFT JOIN object_query_".$classIdEvUs." oqeu ON ore.dest_id = oqeu.oo_id
	            WHERE ore.fieldname = 'users'
        ");
	    var_dump($fieldsArray);exit;

	    $queryBuilder = new \Pimcore\Db\ZendCompatibility\QueryBuilder();
	    $evenings->onCreateQuery(function ($queryBuilder) use ($evenings) {
		    // echo query
		    echo $queryBuilder;exit;
	    });
	    */

	    return array(
	    	//'evenings'      => $evenings,
		    'results'       => $results,
		    'year'          => $year,
		    'monthnames'    => $monthNames,
		    'daynames'      => $dayNames,
		    'overalls'      => $overalls
	    );
    }

	public function oneEveningAction(Request $request)
	{
		$eveningId = $request->get('id');
		// if there is no evening id given, load latest one
		if ($eveningId) {
			$evening = \Pimcore\Model\Object::getById($eveningId);
		} else {
			//$year = $request->get('year', date('Y'));
			$evenings = new DataObject\NinepinsEvening\Listing();
			//$evenings->setCondition("date >= ?", $year."%");
			$evenings->setOrderKey("date");
			$evenings->setOrder("desc");
			$evenings->setLimit(1);
			$evenings->load();

			$eveningResults = $evenings->getObjects();
			$evening = $eveningResults[0];
		}

		if ($request->isMethod('post')) {
			$params = $request->request->all();
			$form = $params['form'];

			$date = date('YmdHis');
			if ($form == 1) {
				$userId = $params['user'];

				$gameEvent = new DataObject\GameEvent();
				$gameEvent->setKey(\Pimcore\File::getValidFilename($date.'-Wurf-'.$params['etype']));
				$gameEvent->setParentId($userId);
				//$gameEvent->setName("Wurf am ".$date);
				//$gameEvent->setDescription("Wurf mit Typ ".$params['etype']." von User ".$userId);

				$gameEvent->setEtype($params['etype']);
				$gameEvent->setEvening($evening);

				$user = DataObject\User::getById($userId);
				$gameEvent->setUser($user);
				$gameEvent->setPublished(true);

				$gameEvent->save();
			} elseif ($form == 2) {
				$userId = $params['user2'];
				$neuner = $params['neuner'];
				$kraenze = $params['kraenze'];
				$banden = $params['banden'];
				$isBahnmeister = $params['isBahnmeister'];
				$bahnmeisterResult = $params['bahnmeisterResult'];
				$isSonnenkoenig = $params['isSonnenkoenig'];
				$sonnenkoenigResult = $params['sonnenkoenigResult'];

				$eveningUser = new DataObject\EveningUser();
				$eveningUser->setKey(\Pimcore\File::getValidFilename($date.'-EveningUser-'.$evening->getId().'-'.$userId));
				$eveningUser->setParentId($userId);

				$eveningUser->setNeuner($neuner);
				$eveningUser->setKraenze($kraenze);
				$eveningUser->setPudel($banden);
				$eveningUser->setIsBahnmeister($isBahnmeister);
				$eveningUser->setBahnmeisterResult($bahnmeisterResult);
				$eveningUser->setIsSonnenkoenig($isSonnenkoenig);
				$eveningUser->setSonnenkoenigResult($sonnenkoenigResult);

				$user = DataObject\User::getById($userId);
				$eveningUser->setUser($user);
				$eveningUser->setEvening($evening);
				$eveningUser->setPublished(true);

				$eveningUser->save();
			}
		}

		if (!$evening) {
			$this->redirect('http://www.club55.de');
		}

		$eveningGames = $evening->getGames();
		$eveningGamesArr = array();
		foreach ($eveningGames as $evGame) {
			$eveningGamesArr[$evGame->getId()] = $evGame;
		}

		$games = new DataObject\Game\Listing();
		$games->load();

		$eveningUsers = $evening->getUsers();
		$eveningUsersArr = array();
		foreach ($eveningUsers as $evUser) {
			$eveningUsersArr[$evUser->getId()] = $evUser;
		}

		$users = new DataObject\User\Listing();
		$users->load();

		$eventsData = $this->getEveningEvents($evening);

		$year = $request->get('year', date('Y'));
		$evenings = new DataObject\NinepinsEvening\Listing();
		$evenings->setCondition("date >= ?", $year."%");
		$evenings->setOrderKey("date");
		$evenings->setOrder("asc");
		$evenings->load();

		return array(
			'evening'       => $evening,
			'eveningGames'  => $eveningGamesArr,
			'games'         => $games,
			'eveningUsers'  => $eveningUsersArr,
			'users'         => $users,
			'neuner'        => $eventsData['neuner'],
			'kraenze'       => $eventsData['kraenze'],
			'banden'        => $eventsData['banden'],
			'evenings'      => $evenings,
			'year'          => $year,
			'monthnames'    => $this->getMonthNames(),
			'daynames'      => $this->getDayNames()
		);

		/*
		return $this->render(':Default:oneEvening.html.twig', array(
			'editmode' => false,
			'evenings' => array(
				0 => array('id' => 1),
				1 => array('id' => 2),
				2 => array('id' => 3),
			)
		));
		*/
	}

	public function gamesAction()
	{
		$games = new DataObject\Game\Listing();
		$games->load();

		return array(
			'games' => $games
		);
	}

	public function footerAction()
	{

	}

	private function getEveningEvents($evening)
	{
		// get all events
		$events = new DataObject\GameEvent\Listing();
		$events->setCondition("evening__id = :evening", array(
			'evening' => $evening->getId()
		));

		$neuner = array();
		$kraenze = array();
		$banden = array();

		foreach($events->getObjects() as $event) {
			switch($event->getEtype()) {
				case(1):
					array_push($neuner, $event);
					break;
				case(2):
					array_push($kraenze, $event);
					break;
				case(3):
					array_push($banden, $event);
					break;
				default:
					break;
			}
		}

		return array(
			'neuner'    => $neuner,
			'kraenze'   => $kraenze,
			'banden'    => $banden
		);
	}

	private function getMonthNames()
	{
		return array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
	}

	private function getDayNames()
	{
		return array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
	}
}
