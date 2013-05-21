<?php

class Application_Model_user {

    protected $db, $card;

    public function __construct() {
        $this->db = Zend_Registry::get('db');

    }
    
    
    public function getUserGroup($user_id) {

        /*
         *

          ALTER TABLE `cms_user`
          ADD COLUMN `id_group`  int(7) NULL AFTER `pasword`;

         * 
         */

        $sql = $this->db->select()
                ->from('cms_user')
                ->where('cms_user.id = ?', $user_id);
        $aData = $this->db->fetchRow($sql);

        return $aData['id_group'];
    }

    

    public function getCountries() {
        $countries = array();
        $countries['NL'] = 'Nederland';
        $countries['AF'] = 'Afghanistan';
        $countries['AX'] = 'Ålandseilanden';
        $countries['AL'] = 'Albanië';
        $countries['DZ'] = 'Algerije';
        $countries['AS'] = 'Amerikaans-Samoa';
        $countries['VI'] = 'Amerikaanse Maagdeneilanden';
        $countries['AD'] = 'Andorra';
        $countries['AO'] = 'Angola';
        $countries['AI'] = 'Anguilla';
        $countries['AQ'] = 'Antarctica';
        $countries['AG'] = 'Antigua en Barbuda';
        $countries['AR'] = 'Argentinië';
        $countries['AM'] = 'Armenië';
        $countries['AW'] = 'Aruba';
        $countries['AU'] = 'Australië';
        $countries['AZ'] = 'Azerbeidzjan';
        $countries['BS'] = 'Bahama\'s';
        $countries['BH'] = 'Bahrein';
        $countries['BD'] = 'Bangladesh';
        $countries['BB'] = 'Barbados';
        $countries['BE'] = 'België';
        $countries['BZ'] = 'Belize';
        $countries['BJ'] = 'Benin';
        $countries['BM'] = 'Bermuda';
        $countries['BT'] = 'Bhutan';
        $countries['BO'] = 'Bolivia';
        $countries['BA'] = 'Bosnië en Herzegovina';
        $countries['BW'] = 'Botswana';
        $countries['BV'] = 'Bouvet';
        $countries['BR'] = 'Brazilië';
        $countries['IO'] = 'Brits Territorium in de Indische Oceaan';
        $countries['VG'] = 'Britse Maagdeneilanden';
        $countries['BN'] = 'Brunei';
        $countries['BG'] = 'Bulgarije';
        $countries['BF'] = 'Burkina Faso';
        $countries['BI'] = 'Burundi';
        $countries['KH'] = 'Cambodja';
        $countries['CA'] = 'Canada';
        $countries['CF'] = 'Centraal-Afrikaanse Republiek';
        $countries['CL'] = 'Chili';
        $countries['CN'] = 'China';
        $countries['CX'] = 'Christmaseiland';
        $countries['CC'] = 'Cocoseilanden';
        $countries['CO'] = 'Colombia';
        $countries['KM'] = 'Comoren';
        $countries['CG'] = 'Congo-Brazzaville';
        $countries['CD'] = 'Congo-Kinshasa';
        $countries['CK'] = 'Cookeilanden';
        $countries['CR'] = 'Costa Rica';
        $countries['CU'] = 'Cuba';
        $countries['CY'] = 'Cyprus';
        $countries['DK'] = 'Denemarken';
        $countries['DJ'] = 'Djibouti';
        $countries['DM'] = 'Dominica';
        $countries['DO'] = 'Dominicaanse Republiek';
        $countries['DE'] = 'Duitsland';
        $countries['EC'] = 'Ecuador';
        $countries['EG'] = 'Egypte';
        $countries['SV'] = 'El Salvador';
        $countries['GQ'] = 'Equatoriaal-Guinea';
        $countries['ER'] = 'Eritrea';
        $countries['EE'] = 'Estland';
        $countries['ET'] = 'Ethiopië';
        $countries['FO'] = 'Faeröer';
        $countries['FK'] = 'Falklandeilanden';
        $countries['FJ'] = 'Fiji';
        $countries['PH'] = 'Filipijnen';
        $countries['FI'] = 'Finland';
        $countries['FR'] = 'Frankrijk';
        $countries['GF'] = 'Frans-Guyana';
        $countries['PF'] = 'Frans-Polynesië';
        $countries['TF'] = 'Franse Zuidelijke en Antarctische Gebieden';
        $countries['GA'] = 'Gabon';
        $countries['GM'] = 'Gambia';
        $countries['GE'] = 'Georgië';
        $countries['GH'] = 'Ghana';
        $countries['GI'] = 'Gibraltar';
        $countries['GD'] = 'Grenada';
        $countries['GR'] = 'Griekenland';
        $countries['GL'] = 'Groenland';
        $countries['GP'] = 'Guadeloupe';
        $countries['GG'] = 'Guernsey';
        $countries['GU'] = 'Guam';
        $countries['GT'] = 'Guatemala';
        $countries['GN'] = 'Guinee';
        $countries['GW'] = 'Guinee-Bissau';
        $countries['GY'] = 'Guyana';
        $countries['HT'] = 'Haïti';
        $countries['HM'] = 'Heard en McDonaldeilanden';
        $countries['HN'] = 'Honduras';
        $countries['HU'] = 'Hongarije';
        $countries['HK'] = 'Hongkong';
        $countries['IE'] = 'Ierland';
        $countries['IS'] = 'IJsland';
        $countries['IN'] = 'India';
        $countries['ID'] = 'Indonesië';
        $countries['IQ'] = 'Irak';
        $countries['IR'] = 'Iran';
        $countries['IM'] = 'Isle of Man';
        $countries['IL'] = 'Israël';
        $countries['IT'] = 'Italië';
        $countries['CI'] = 'Ivoorkust';
        $countries['JM'] = 'Jamaica';
        $countries['JP'] = 'Japan';
        $countries['YE'] = 'Jemen';
        $countries['JE'] = 'Jersey';
        $countries['JO'] = 'Jordanië';
        $countries['KY'] = 'Kaaimaneilanden';
        $countries['CV'] = 'Kaapverdië';
        $countries['CM'] = 'Kameroen';
        $countries['KZ'] = 'Kazachstan';
        $countries['KE'] = 'Kenia';
        $countries['KG'] = 'Kirgizië';
        $countries['KI'] = 'Kiribati';
        $countries['KW'] = 'Koeweit';
        $countries['HR'] = 'Kroatië';
        $countries['LA'] = 'Laos';
        $countries['LS'] = 'Lesotho';
        $countries['LV'] = 'Letland';
        $countries['LB'] = 'Libanon';
        $countries['LR'] = 'Liberia';
        $countries['LY'] = 'Libië';
        $countries['LI'] = 'Liechtenstein';
        $countries['LT'] = 'Litouwen';
        $countries['LU'] = 'Luxemburg';
        $countries['MO'] = 'Macau';
        $countries['MK'] = 'Macedonië';
        $countries['MG'] = 'Madagaskar';
        $countries['MW'] = 'Malawi';
        $countries['MV'] = 'Maldiven';
        $countries['MY'] = 'Maleisië';
        $countries['ML'] = 'Mali';
        $countries['MT'] = 'Malta';
        $countries['MA'] = 'Marokko';
        $countries['MH'] = 'Marshalleilanden';
        $countries['MQ'] = 'Martinique';
        $countries['MR'] = 'Mauritanië';
        $countries['MU'] = 'Mauritius';
        $countries['YT'] = 'Mayotte';
        $countries['MX'] = 'Mexico';
        $countries['FM'] = 'Micronesia';
        $countries['MD'] = 'Moldavië';
        $countries['MC'] = 'Monaco';
        $countries['MN'] = 'Mongolië';
        $countries['ME'] = 'Montenegro';
        $countries['MS'] = 'Montserrat';
        $countries['MZ'] = 'Mozambique';
        $countries['MM'] = 'Myanmar';
        $countries['NA'] = 'Namibië';
        $countries['NR'] = 'Nauru';
        $countries['AN'] = 'Nederlandse Antillen';
        $countries['NP'] = 'Nepal';
        $countries['NI'] = 'Nicaragua';
        $countries['NC'] = 'Nieuw-Caledonië';
        $countries['NZ'] = 'Nieuw-Zeeland';
        $countries['NE'] = 'Niger';
        $countries['NG'] = 'Nigeria';
        $countries['NU'] = 'Niue';
        $countries['MP'] = 'Noordelijke Marianen';
        $countries['KP'] = 'Noord-Korea';
        $countries['NO'] = 'Noorwegen';
        $countries['NF'] = 'Norfolk';
        $countries['UG'] = 'Oeganda';
        $countries['UA'] = 'Oekraïne';
        $countries['UZ'] = 'Oezbekistan';
        $countries['OM'] = 'Oman';
        $countries['AT'] = 'Oostenrijk';
        $countries['TL'] = 'Oost-Timor';
        $countries['PK'] = 'Pakistan';
        $countries['PW'] = 'Palau';
        $countries['PS'] = 'Palestijnse Autoriteit';
        $countries['PA'] = 'Panama';
        $countries['PG'] = 'Papoea-Nieuw-Guinea';
        $countries['PY'] = 'Paraguay';
        $countries['PE'] = 'Peru';
        $countries['PN'] = 'Pitcairneilanden';
        $countries['PL'] = 'Polen';
        $countries['PT'] = 'Portugal';
        $countries['PR'] = 'Puerto Rico';
        $countries['QA'] = 'Qatar';
        $countries['RE'] = 'Réunion';
        $countries['RO'] = 'Roemenië';
        $countries['RU'] = 'Rusland';
        $countries['RW'] = 'Rwanda';
        $countries['BL'] = 'Saint-Barthélemy';
        $countries['KN'] = 'Saint Kitts en Nevis';
        $countries['LC'] = 'Saint Lucia';
        $countries['PM'] = 'Saint-Pierre en Miquelon';
        $countries['VC'] = 'Saint Vincent en de Grenadines';
        $countries['SB'] = 'Salomonseilanden';
        $countries['WS'] = 'Samoa';
        $countries['SM'] = 'San Marino';
        $countries['ST'] = 'Sao Tomé en Principe';
        $countries['SA'] = 'Saoedi-Arabië';
        $countries['SN'] = 'Senegal';
        $countries['RS'] = 'Servië';
        $countries['SC'] = 'Seychellen';
        $countries['SL'] = 'Sierra Leone';
        $countries['SG'] = 'Singapore';
        $countries['SH'] = 'Sint-Helena';
        $countries['MF'] = 'Sint-Maarten';
        $countries['SI'] = 'Slovenië';
        $countries['SK'] = 'Slowakije';
        $countries['SO'] = 'Somalië';
        $countries['ES'] = 'Spanje';
        $countries['LK'] = 'Sri Lanka';
        $countries['SD'] = 'Soedan';
        $countries['SR'] = 'Suriname';
        $countries['SJ'] = 'Jan Mayen';
        $countries['SZ'] = 'Swaziland';
        $countries['SY'] = 'Syrië';
        $countries['TJ'] = 'Tadzjikistan';
        $countries['TW'] = 'Taiwan';
        $countries['TZ'] = 'Tanzania';
        $countries['TH'] = 'Thailand';
        $countries['TG'] = 'Togo';
        $countries['TK'] = 'Tokelau-eilanden';
        $countries['TO'] = 'Tonga';
        $countries['TT'] = 'Trinidad en Tobago';
        $countries['TD'] = 'Tsjaad';
        $countries['CZ'] = 'Tsjechië';
        $countries['TN'] = 'Tunesië';
        $countries['TR'] = 'Turkije';
        $countries['TM'] = 'Turkmenistan';
        $countries['TC'] = 'Turks- en Caicoseilanden';
        $countries['TV'] = 'Tuvalu';
        $countries['UM'] = 'Kleine Pacifische eilanden van de Verenigde Staten';
        $countries['UY'] = 'Uruguay';
        $countries['VU'] = 'Vanuatu';
        $countries['VA'] = 'Vaticaanstad';
        $countries['VE'] = 'Venezuela';
        $countries['AE'] = 'Verenigde Arabische Emiraten';
        $countries['GB'] = 'Verenigd Koninkrijk';
        $countries['US'] = 'Verenigde Staten';
        $countries['VN'] = 'Vietnam';
        $countries['WF'] = 'Wallis en Futuna';
        $countries['EH'] = 'Westelijke Sahara';
        $countries['BY'] = 'Wit-Rusland';
        $countries['ZM'] = 'Zambia';
        $countries['ZW'] = 'Zimbabwe';
        $countries['ZA'] = 'Zuid-Afrika';
        $countries['GS'] = 'Zuid-Georgië en de Zuidelijke Sandwicheilanden';
        $countries['KR'] = 'Zuid-Korea';
        $countries['SE'] = 'Zweden';
        $countries['CH'] = 'Zwitserland';
        return $countries;
    }

}