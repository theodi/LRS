<?php

$countries = getCountries();
echo json_encode($countries);


function getCountries() {
	$c['ad']['flag']='ad';
	$c['ae']['flag']='ae';
	$c['af']['flag']='af';
	$c['ag']['flag']='ag';
	$c['ai']['flag']='ai';
	$c['al']['flag']='al';
	$c['am']['flag']='am';
	$c['an']['flag']='an';
	$c['ao']['flag']='ao';
	$c['aq']['flag']='aq';
	$c['ar']['flag']='ar';
	$c['as']['flag']='as';
	$c['at']['flag']='at';
	$c['au']['flag']='au';
	$c['aw']['flag']='aw';
	$c['ax']['flag']='ax';
	$c['az']['flag']='az';
	$c['ba']['flag']='ba';
	$c['bb']['flag']='bb';
	$c['bd']['flag']='bd';
	$c['be']['flag']='be';
	$c['bf']['flag']='bf';
	$c['bg']['flag']='bg';
	$c['bh']['flag']='bh';
	$c['bi']['flag']='bi';
	$c['bj']['flag']='bj';
	$c['bm']['flag']='bm';
	$c['bn']['flag']='bn';
	$c['bo']['flag']='bo';
	$c['br']['flag']='br';
	$c['bs']['flag']='bs';
	$c['bt']['flag']='bt';
	$c['bv']['flag']='bv';
	$c['bw']['flag']='bw';
	$c['by']['flag']='by';
	$c['bz']['flag']='bz';
	$c['ca']['flag']='ca';
	$c['cc']['flag']='cc';
	$c['cd']['flag']='cd';
	$c['cf']['flag']='cf';
	$c['cg']['flag']='cg';
	$c['ch']['flag']='ch';
	$c['ci']['flag']='ci';
	$c['ck']['flag']='ck';
	$c['cl']['flag']='cl';
	$c['cm']['flag']='cm';
	$c['cn']['flag']='cn';
	$c['co']['flag']='co';
	$c['cr']['flag']='cr';
	$c['cs']['flag']='cs';
	$c['cu']['flag']='cu';
	$c['cv']['flag']='cv';
	$c['cx']['flag']='cx';
	$c['cy']['flag']='cy';
	$c['cz']['flag']='cz';
	$c['de']['flag']='de';
	$c['dj']['flag']='dj';
	$c['dk']['flag']='dk';
	$c['dm']['flag']='dm';
	$c['do']['flag']='do';
	$c['dz']['flag']='dz';
	$c['ec']['flag']='ec';
	$c['ee']['flag']='ee';
	$c['eg']['flag']='eg';
	$c['eh']['flag']='eh';
	$c['er']['flag']='er';
	$c['es']['flag']='es';
	$c['et']['flag']='et';
	$c['fi']['flag']='fi';
	$c['fj']['flag']='fj';
	$c['fk']['flag']='fk';
	$c['fm']['flag']='fm';
	$c['fo']['flag']='fo';
	$c['fr']['flag']='fr';
	$c['fx']['flag']='fx';
	$c['ga']['flag']='ga';
	$c['gb']['flag']='gb';
	$c['gd']['flag']='gd';
	$c['ge']['flag']='ge';
	$c['gf']['flag']='gf';
	$c['gh']['flag']='gh';
	$c['gi']['flag']='gi';
	$c['gl']['flag']='gl';
	$c['gm']['flag']='gm';
	$c['gn']['flag']='gn';
	$c['gp']['flag']='gp';
	$c['gq']['flag']='gq';
	$c['gr']['flag']='gr';
	$c['gs']['flag']='gs';
	$c['gt']['flag']='gt';
	$c['gu']['flag']='gu';
	$c['gw']['flag']='gw';
	$c['gy']['flag']='gy';
	$c['hk']['flag']='hk';
	$c['hm']['flag']='hm';
	$c['hn']['flag']='hn';
	$c['hr']['flag']='hr';
	$c['ht']['flag']='ht';
	$c['hu']['flag']='hu';
	$c['id']['flag']='id';
	$c['ie']['flag']='ie';
	$c['il']['flag']='il';
	$c['in']['flag']='in';
	$c['io']['flag']='io';
	$c['iq']['flag']='iq';
	$c['ir']['flag']='ir';
	$c['is']['flag']='is';
	$c['it']['flag']='it';
	$c['jm']['flag']='jm';
	$c['jo']['flag']='jo';
	$c['jp']['flag']='jp';
	$c['ke']['flag']='ke';
	$c['kg']['flag']='kg';
	$c['kh']['flag']='kh';
	$c['ki']['flag']='ki';
	$c['km']['flag']='km';
	$c['kn']['flag']='kn';
	$c['kp']['flag']='kp';
	$c['kr']['flag']='kr';
	$c['kw']['flag']='kw';
	$c['ky']['flag']='ky';
	$c['kz']['flag']='kz';
	$c['la']['flag']='la';
	$c['lb']['flag']='lb';
	$c['lc']['flag']='lc';
	$c['li']['flag']='li';
	$c['lk']['flag']='lk';
	$c['lr']['flag']='lr';
	$c['ls']['flag']='ls';
	$c['lt']['flag']='lt';
	$c['lu']['flag']='lu';
	$c['lv']['flag']='lv';
	$c['ly']['flag']='ly';
	$c['ma']['flag']='ma';
	$c['mc']['flag']='mc';
	$c['md']['flag']='md';
	$c['mg']['flag']='mg';
	$c['mh']['flag']='mh';
	$c['mk']['flag']='mk';
	$c['ml']['flag']='ml';
	$c['mm']['flag']='mm';
	$c['mn']['flag']='mn';
	$c['mo']['flag']='mo';
	$c['mp']['flag']='mp';
	$c['mq']['flag']='mq';
	$c['mr']['flag']='mr';
	$c['ms']['flag']='ms';
	$c['mt']['flag']='mt';
	$c['mu']['flag']='mu';
	$c['mv']['flag']='mv';
	$c['mw']['flag']='mw';
	$c['mx']['flag']='mx';
	$c['my']['flag']='my';
	$c['mz']['flag']='mz';
	$c['na']['flag']='na';
	$c['nc']['flag']='nc';
	$c['ne']['flag']='ne';
	$c['nf']['flag']='nf';
	$c['ng']['flag']='ng';
	$c['ni']['flag']='ni';
	$c['nl']['flag']='nl';
	$c['no']['flag']='no';
	$c['np']['flag']='np';
	$c['nr']['flag']='nr';
	$c['nu']['flag']='nu';
	$c['nz']['flag']='nz';
	$c['om']['flag']='om';
	$c['pa']['flag']='pa';
	$c['pe']['flag']='pe';
	$c['pf']['flag']='pf';
	$c['pg']['flag']='pg';
	$c['ph']['flag']='ph';
	$c['pk']['flag']='pk';
	$c['pl']['flag']='pl';
	$c['pm']['flag']='pm';
	$c['pn']['flag']='pn';
	$c['pr']['flag']='pr';
	$c['ps']['flag']='ps';
	$c['pt']['flag']='pt';
	$c['pw']['flag']='pw';
	$c['py']['flag']='py';
	$c['qa']['flag']='qa';
	$c['re']['flag']='re';
	$c['ro']['flag']='ro';
	$c['ru']['flag']='ru';
	$c['rw']['flag']='rw';
	$c['sa']['flag']='sa';
	$c['sb']['flag']='sb';
	$c['sc']['flag']='sc';
	$c['sd']['flag']='sd';
	$c['se']['flag']='se';
	$c['sg']['flag']='sg';
	$c['sh']['flag']='sh';
	$c['si']['flag']='si';
	$c['sj']['flag']='sj';
	$c['sk']['flag']='sk';
	$c['sl']['flag']='sl';
	$c['sm']['flag']='sm';
	$c['sn']['flag']='sn';
	$c['so']['flag']='so';
	$c['sr']['flag']='sr';
	$c['st']['flag']='st';
	$c['su']['flag']='su';
	$c['sv']['flag']='sv';
	$c['sy']['flag']='sy';
	$c['sz']['flag']='sz';
	$c['tc']['flag']='tc';
	$c['td']['flag']='td';
	$c['tf']['flag']='tf';
	$c['tg']['flag']='tg';
	$c['th']['flag']='th';
	$c['tj']['flag']='tj';
	$c['tk']['flag']='tk';
	$c['tl']['flag']='tl';
	$c['tm']['flag']='tm';
	$c['tn']['flag']='tn';
	$c['to']['flag']='to';
	$c['tp']['flag']='tp';
	$c['tr']['flag']='tr';
	$c['tt']['flag']='tt';
	$c['tv']['flag']='tv';
	$c['tw']['flag']='tw';
	$c['tz']['flag']='tz';
	$c['uk']['flag']='uk';
	$c['ug']['flag']='ug';
	$c['gb']['flag']='gb';
	$c['um']['flag']='um';
	$c['us']['flag']='us';
	$c['uy']['flag']='uy';
	$c['uz']['flag']='uz';
	$c['va']['flag']='va';
	$c['vc']['flag']='vc';
	$c['ve']['flag']='ve';
	$c['vg']['flag']='vg';
	$c['vi']['flag']='vi';
	$c['vn']['flag']='vn';
	$c['vu']['flag']='vu';
	$c['wf']['flag']='wf';
	$c['ws']['flag']='ws';
	$c['ye']['flag']='ye';
	$c['yt']['flag']='yt';
	$c['yu']['flag']='yu';
	$c['za']['flag']='za';
	$c['zm']['flag']='zm';
	$c['zr']['flag']='zr';
	$c['zw']['flag']='zw';

	$c['ad']['name']='Andorra';
	$c['ae']['name']='United Arab Emirates';
	$c['af']['name']='Afghanistan';
	$c['ag']['name']='Antigua and Barbuda';
	$c['ai']['name']='Anguilla';
	$c['al']['name']='Albania';
	$c['am']['name']='Armenia';
	$c['an']['name']='Netherlands Antilles';
	$c['ao']['name']='Angola';
	$c['aq']['name']='Antarctica';
	$c['ar']['name']='Argentina';
	$c['as']['name']='American Samoa';
	$c['at']['name']='Austria';
	$c['au']['name']='Australia';
	$c['aw']['name']='Aruba';
	$c['ax']['name']='Aland Islands';
	$c['az']['name']='Azerbaijan';
	$c['ba']['name']='Bosnia and Herzegovina';
	$c['bb']['name']='Barbados';
	$c['bd']['name']='Bangladesh';
	$c['be']['name']='Belgium';
	$c['bf']['name']='Burkina Faso';
	$c['bg']['name']='Bulgaria';
	$c['bh']['name']='Bahrain';
	$c['bi']['name']='Burundi';
	$c['bj']['name']='Benin';
	$c['bm']['name']='Bermuda';
	$c['bn']['name']='Brunei Darussalam';
	$c['bo']['name']='Bolivia';
	$c['br']['name']='Brazil';
	$c['bs']['name']='Bahamas';
	$c['bt']['name']='Bhutan';
	$c['bv']['name']='Bouvet Island';
	$c['bw']['name']='Botswana';
	$c['by']['name']='Belarus';
	$c['bz']['name']='Belize';
	$c['ca']['name']='Canada';
	$c['cc']['name']='Cocos (Keeling) Islands';
	$c['cd']['name']='Democratic Republic of the Congo';
	$c['cf']['name']='Central African Republic';
	$c['cg']['name']='Congo';
	$c['ch']['name']='Switzerland';
	$c['ci']['name']="Cote D'Ivoire (Ivory Coast)";
	$c['ck']['name']='Cook Islands';
	$c['cl']['name']='Chile';
	$c['cm']['name']='Cameroon';
	$c['cn']['name']='China';
	$c['co']['name']='Colombia';
	$c['cr']['name']='Costa Rica';
	$c['cs']['name']='Serbia and Montenegro';
	$c['cu']['name']='Cuba';
	$c['cv']['name']='Cape Verde';
	$c['cx']['name']='Christmas Island';
	$c['cy']['name']='Cyprus';
	$c['cz']['name']='Czech Republic';
	$c['de']['name']='Germany';
	$c['dj']['name']='Djibouti';
	$c['dk']['name']='Denmark';
	$c['dm']['name']='Dominica';
	$c['do']['name']='Dominican Republic';
	$c['dz']['name']='Algeria';
	$c['ec']['name']='Ecuador';
	$c['ee']['name']='Estonia';
	$c['eg']['name']='Egypt';
	$c['eh']['name']='Western Sahara';
	$c['er']['name']='Eritrea';
	$c['es']['name']='Spain';
	$c['et']['name']='Ethiopia';
	$c['fi']['name']='Finland';
	$c['fj']['name']='Fiji';
	$c['fk']['name']='Falkland Islands (Malvinas)';
	$c['fm']['name']='Federated States of Micronesia';
	$c['fo']['name']='Faroe Islands';
	$c['fr']['name']='France';
	$c['fx']['name']='France, Metropolitan';
	$c['ga']['name']='Gabon';
	$c['gb']['name']='Great Britain (UK)';
	$c['gd']['name']='Grenada';
	$c['ge']['name']='Georgia';
	$c['gf']['name']='French Guiana';
	$c['gh']['name']='Ghana';
	$c['gi']['name']='Gibraltar';
	$c['gl']['name']='Greenland';
	$c['gm']['name']='Gambia';
	$c['gn']['name']='Guinea';
	$c['gp']['name']='Guadeloupe';
	$c['gq']['name']='Equatorial Guinea';
	$c['gr']['name']='Greece';
	$c['gs']['name']='S. Georgia and S. Sandwich Islands';
	$c['gt']['name']='Guatemala';
	$c['gu']['name']='Guam';
	$c['gw']['name']='Guinea-Bissau';
	$c['gy']['name']='Guyana';
	$c['hk']['name']='Hong Kong';
	$c['hm']['name']='Heard Island and McDonald Islands';
	$c['hn']['name']='Honduras';
	$c['hr']['name']='Croatia (Hrvatska)';
	$c['ht']['name']='Haiti';
	$c['hu']['name']='Hungary';
	$c['id']['name']='Indonesia';
	$c['ie']['name']='Ireland';
	$c['il']['name']='Israel';
	$c['in']['name']='India';
	$c['io']['name']='British Indian Ocean Territory';
	$c['iq']['name']='Iraq';
	$c['ir']['name']='Iran';
	$c['is']['name']='Iceland';
	$c['it']['name']='Italy';
	$c['jm']['name']='Jamaica';
	$c['jo']['name']='Jordan';
	$c['jp']['name']='Japan';
	$c['ke']['name']='Kenya';
	$c['kg']['name']='Kyrgyzstan';
	$c['kh']['name']='Cambodia';
	$c['ki']['name']='Kiribati';
	$c['km']['name']='Comoros';
	$c['kn']['name']='Saint Kitts and Nevis';
	$c['kp']['name']='Korea (North)';
	$c['kr']['name']='Korea (South)';
	$c['kw']['name']='Kuwait';
	$c['ky']['name']='Cayman Islands';
	$c['kz']['name']='Kazakhstan';
	$c['la']['name']='Laos';
	$c['lb']['name']='Lebanon';
	$c['lc']['name']='Saint Lucia';
	$c['li']['name']='Liechtenstein';
	$c['lk']['name']='Sri Lanka';
	$c['lr']['name']='Liberia';
	$c['ls']['name']='Lesotho';
	$c['lt']['name']='Lithuania';
	$c['lu']['name']='Luxembourg';
	$c['lv']['name']='Latvia';
	$c['ly']['name']='Libya';
	$c['ma']['name']='Morocco';
	$c['mc']['name']='Monaco';
	$c['md']['name']='Moldova';
	$c['mg']['name']='Madagascar';
	$c['mh']['name']='Marshall Islands';
	$c['mk']['name']='Macedonia';
	$c['ml']['name']='Mali';
	$c['mm']['name']='Myanmar';
	$c['mn']['name']='Mongolia';
	$c['mo']['name']='Macao';
	$c['mp']['name']='Northern Mariana Islands';
	$c['mq']['name']='Martinique';
	$c['mr']['name']='Mauritania';
	$c['ms']['name']='Montserrat';
	$c['mt']['name']='Malta';
	$c['mu']['name']='Mauritius';
	$c['mv']['name']='Maldives';
	$c['mw']['name']='Malawi';
	$c['mx']['name']='Mexico';
	$c['my']['name']='Malaysia';
	$c['mz']['name']='Mozambique';
	$c['na']['name']='Namibia';
	$c['nc']['name']='New Caledonia';
	$c['ne']['name']='Niger';
	$c['nf']['name']='Norfolk Island';
	$c['ng']['name']='Nigeria';
	$c['ni']['name']='Nicaragua';
	$c['nl']['name']='Netherlands';
	$c['no']['name']='Norway';
	$c['np']['name']='Nepal';
	$c['nr']['name']='Nauru';
	$c['nu']['name']='Niue';
	$c['nz']['name']='New Zealand (Aotearoa)';
	$c['om']['name']='Oman';
	$c['pa']['name']='Panama';
	$c['pe']['name']='Peru';
	$c['pf']['name']='French Polynesia';
	$c['pg']['name']='Papua New Guinea';
	$c['ph']['name']='Philippines';
	$c['pk']['name']='Pakistan';
	$c['pl']['name']='Poland';
	$c['pm']['name']='Saint Pierre and Miquelon';
	$c['pn']['name']='Pitcairn';
	$c['pr']['name']='Puerto Rico';
	$c['ps']['name']='Palestinian Territory';
	$c['pt']['name']='Portugal';
	$c['pw']['name']='Palau';
	$c['py']['name']='Paraguay';
	$c['qa']['name']='Qatar';
	$c['re']['name']='Reunion';
	$c['ro']['name']='Romania';
	$c['ru']['name']='Russian Federation';
	$c['rw']['name']='Rwanda';
	$c['sa']['name']='Saudi Arabia';
	$c['sb']['name']='Solomon Islands';
	$c['sc']['name']='Seychelles';
	$c['sd']['name']='Sudan';
	$c['se']['name']='Sweden';
	$c['sg']['name']='Singapore';
	$c['sh']['name']='Saint Helena';
	$c['si']['name']='Slovenia';
	$c['sj']['name']='Svalbard and Jan Mayen';
	$c['sk']['name']='Slovakia';
	$c['sl']['name']='Sierra Leone';
	$c['sm']['name']='San Marino';
	$c['sn']['name']='Senegal';
	$c['so']['name']='Somalia';
	$c['sr']['name']='Suriname';
	$c['st']['name']='Sao Tome and Principe';
	$c['su']['name']='USSR (former)';
	$c['sv']['name']='El Salvador';
	$c['sy']['name']='Syria';
	$c['sz']['name']='Swaziland';
	$c['tc']['name']='Turks and Caicos Islands';
	$c['td']['name']='Chad';
	$c['tf']['name']='French Southern Territories';
	$c['tg']['name']='Togo';
	$c['th']['name']='Thailand';
	$c['tj']['name']='Tajikistan';
	$c['tk']['name']='Tokelau';
	$c['tl']['name']='Timor-Leste';
	$c['tm']['name']='Turkmenistan';
	$c['tn']['name']='Tunisia';
	$c['to']['name']='Tonga';
	$c['tp']['name']='East Timor';
	$c['tr']['name']='Turkey';
	$c['tt']['name']='Trinidad and Tobago';
	$c['tv']['name']='Tuvalu';
	$c['tw']['name']='Taiwan';
	$c['tz']['name']='Tanzania';
	$c['uk']['name']='Ukraine';
	$c['ug']['name']='Uganda';
	$c['gb']['name']='United Kingdom';
	$c['um']['name']='United States Minor Outlying Islands';
	$c['us']['name']='United States';
	$c['uy']['name']='Uruguay';
	$c['uz']['name']='Uzbekistan';
	$c['va']['name']='Vatican City State (Holy See)';
	$c['vc']['name']='Saint Vincent and the Grenadines';
	$c['ve']['name']='Venezuela';
	$c['vg']['name']='Virgin Islands (British)';
	$c['vi']['name']='Virgin Islands (U.S.)';
	$c['vn']['name']='Viet Nam';
	$c['vu']['name']='Vanuatu';
	$c['wf']['name']='Wallis and Futuna';
	$c['ws']['name']='Samoa';
	$c['ye']['name']='Yemen';
	$c['yt']['name']='Mayotte';
	$c['yu']['name']='Yugoslavia (former)';
	$c['za']['name']='South Africa';
	$c['zm']['name']='Zambia';
	$c['zr']['name']='Zaire (former)';
	$c['zw']['name']='Zimbabwe';

return $c;
}
?>