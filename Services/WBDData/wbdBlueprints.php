<?php

$WBD_USER_RECORD = array(
	//debug:
	'row_id'=>'',
	//"last_wbd_report" => '',
	

	'title' => '', //Herr:Frau
	'degree' => '',
	'first_name' => '',
	'last_name' => '',
	'name_affix' => '',
	'birthday' => '',
	'auth_email' => '',
	'auth_phone_nr' => '',
	'zipcode' => '',
	'city' => '',
	'street' => '',
	'house_number' => '',
	'pob' => '',
	'free_text' => '',
	'email' => '',
	'phone_nr' => '',
	'mobile_phone_nr' => '',
	'url' => '',
	'agent_registration_nr' => '',
	'agency_work' => '',
	'agent_state' => '',
	'internal_agent_id' => '',

	//constant, don't bother:
	'email_confirmation' => 'Nein',
	'tp_service'  => 'Ja',
	'country_code' => 'D',
	'address_code' => 'geschäftlich',
	'data_transfer_code'  => 'Ja',
	'data_protection_code'  => 'Ja',
	'training_pass' => 'Nein'
);



$WBD_EDU_RECORD = array(
	//debug:
	'row_id'=>'',
	//"last_wbd_report" => '',

	"name" => "", //lastname
	"first_name" => "",
	"birthday_or_internal_agent_id" => '', //$record['user_id'],
	"agent_id" => "", //$record['user_bwv_id'],
	"training" => "", // $record['crs_template_title'],
	"from" => "", //date('d.m.Y', $record['crs_start_date']),
	"till" => "", //date('d.m.Y', $record['crs_end_date']),
	"score" => "", //$record['crs_credit_points'],
	"internal_booking_id" => "", //$record['crs_ref_id'],
	"contact_degree" => "",
	"contact_first_name" => "",
	"contact_last_name" => "",
	"contact_phone" => "",
	"contact_email" => "",
	"study_type_selection" => "", // "Präsenzveranstaltung" | "Selbstgesteuertes E-Learning" | "Gesteuertes E-Learning";
	"study_content" => "", //Spartenübergreifend",
	"score_code" => ""
			
);


$VALUE_MAPPINGS = array(
	"course_type" => array(
		"Präsenztraining" => "Präsenzveranstaltung",
		"XX" => "Selbstgesteuertes E-Learning",
		"XX" => "Gesteuertes E-Learning",
	),
	"salutation" => array(
		"m" => "Herr",
		"f" => "Frau",
		"w" => "Frau"
	),
	"agent_status" => array(
		"0 - aus Stellung" => "Sonstiges"
	  , "1 - Angestellter Außendienst" => "Angestellter Außendienst"
	  , "2 - Ausschließlichkeitsvermittler" => "Ausschließlichkeitsvermittler"
	  , "3 - Makler" => "Makler"
	  , "4 - Mehrfachagent" => "Mehrfachagent"
	  , "5 - Mitarbeiter eines Vermittlers" => "Mitarbeiter eines Vermittlers"
	  , "6 - Sonstiges" => "Sonstiges"
	  , "7 - keine Zuordnung"  => "Sonstiges"

	)


);



//static $telno_regexp = "/^((00|[+])49((\s|[-\/])?)|0)1[5-7][0-9]([0-9]?)((\s|[-\/])?)([0-9 ]{7,12})$/";
require_once("Services/GEV/Desktop/classes/class.gevUserProfileGUI.php");

$WBD_USER_RECORD_VALIDATION = array(
	'title' 			=> array('mandatory'=>1,
								 'list'=> array('Herr', 'Frau'))
	,'degree' 			=> array('maxlen' => 20)
	,'first_name' 		=> array('mandatory'=>1, 'maxlen' => 30)
	,'last_name' 		=> array('mandatory'=>1, 'maxlen' => 50)
	,'name_affix' 		=> array('maxlen' => 50)
	//,'birthday' 		=> array('form' => 'REGEX HERE')
	//,'auth_email' 		=> array('form' => 'REGEX HERE')
	,'auth_phone_nr' 	=> array('mandatory'=>1, 
								 'form' => gevUserProfileGUI::$telno_regexp)
	//,'phone_nr'	 		=> array('form' => gevUserProfileGUI::$telno_regexp)
	,'zipcode' 			=> array('mandatory'=>1, 'maxlen' => 30)
	,'city' 			=> array('mandatory'=>1, 'maxlen' => 50)
	,'street' 			=> array('mandatory'=>1, 'maxlen' => 50)
	,'house_number' 	=> array('mandatory'=>1, 'maxlen' => 10)
	,'pob' 				=> array('maxlen' => 30)
	,'free_text'		=> array('maxlen' => 50)
	//,'email' 			=> array('form' => 'REGEX HERE')
	
	,'agency_work' 		=> array('mandatory'=>1, 
								 'list' => array(
								 	'OKZ1',
								 	'OKZ2',
								 	'OKZ3'
								 ))
	,'agent_state' 		=> array('mandatory'=>1, 
								 'list' => array_values($VALUE_MAPPINGS['agent_status'])
								)
);



$CSV_LABELS = array(
	//debug:
	'row_id'=>'ROW-ID',
	"last_wbd_report" => 'LAST_WBD_REPORT',
	

	"address_code" => "Adresskennzeichen",
	"administration_id" => "VerwaltungsID",
	"agency_work" => "Vermittlungstätigkeit",
	"agent_id" => "VermittlerID",
	"agent_id_import" => "Vermittler Id",
	"agent_registration_nr" => "Vermittlerregisternummer",
	"agent_state" => "Vermittlerstatus",
	"auth_email" => "Authentifizierungs Email",
	"auth_phone_nr" => "Authentifizierungs Telefonnummer",
	"birthday" => "Geburtsdatum",
	"birthday_or_internal_agent_id" => "Geburtsdatum (oder interne VermittlerID)",
	"booking_nr" => "Buchungsnummer",
	"cancelled" => "Storniert",
	"city" => "Ort",
	"contact_degree" => "Ansprechpartner Titel",
	"contact_email" => "Ansprechpartner E-Mail",
	"contact_first_name" => "Ansprechpartner Vorname",
	"contact_last_name" => "Ansprechpartner Nachname",
	"contact_phone" => "Ansprechpartner Telefon",
	"country_code" => "Länderkennzeichen",
	"current_okz" => "Aktuelles OKZ",
	"data_protection_code" => "Datenschutzkennzeichen",
	"data_transfer_code" => "Datenübermittlungskennzeichen",
	"degree" => "Titel",
	"email" => "Emailadresse",
	"email_confirmation" => "Benachrichtigung Per Email",
	"first_name" => "Vorname",
	"free_text" => "Freitext",
	"from" => "von",
	"house_number" => "Hausnummer",
	"internal_agent_id" => "Interne Vermittler Id",
	//"internal_agent_id_import" => "interne Vermittler Id",
	"internal_booking_id" => "InterneBuchungsID",
	"internal_booking_id_import" => "interneBuchungsId",
	"last_name" => "Nachname",
	"mobile_phone_nr" => "Mobilfunknummer",
	"name" => "Name",
	"name_affix" => "Namenszusatz",
	"phone_nr" => "Telefonnummer",
	"pob" => "Postfach",
	"score" => "Punkte",
	"score_code" => "KennzeichenPunkte",
	"seminar_end_date" => "SeminarDatumbis",
	"seminar_start_date" => "SeminarDatumab",
	"seminar_title" => "Name der Maßnahme",
	"service_end" => "ServiceBis",
	"service_since" => "ServiceSeit",
	"end_date" => "Enddatum",
	"start_date" => "Startdatum",
	"street" => "Straße",
	"study_content" => "Lerninhalt",
	"study_type" => "Lernart",
	"study_type_selection" => "Lernart (Auswahl)",
	"till" => "bis",
	"title" => "Anrede",
	"tp_service" => "TP Service",
	"training" => "Weiterbildung",
	"training_pass" => "Weiterbildungausweis beantragt",
	"training_score_booking_id" => "WeiterbildungsPunkteBuchungsID",
	"training_score_booking_id_import" => "WeiterbildungspunktebuchungsID",
	"url" => "URL",
	"zipcode" => "Postleitzahl"
);

?>
