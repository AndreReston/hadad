<?php
session_start();
require 'computer.php';
$message = '';

$distance_km = 0;
$delivery_minutes = 0;

// Provinces + Cities (simplified example)
$provinces = [

    "Abra" => ["Bangued","Boliney","Bucay","Bucloc","Daguioman","Danglas","Dolores","La Paz","Lacub","Lagangilang","Lagayan","Langiden","Licuan-Baay","Luba","Malibcong","Manabo","Peñarrubia","Pidigan","Pilar","Sallapadan","San Isidro","San Juan","San Quintin","Tayum","Tineg","Tubo","Villaviciosa"],

    "Agusan del Norte" => ["Baculin", "Cabadbaran", "Jabonga", "Kitcharao", "Las Nieves", "Magallanes", "Nasipit", "Santiago", "Tubay", "Butuan City"],
    "Agusan del Sur" => ["Bayugan City","Bunawan", "Esperanza","La Paz","Loreto","Prosperidad","Rosario","San Francisco","San Luis","Santa Josefa", "Sibagat", "Talacogon","Trento", "Veruela"],
    "Aklan" => ["Altavas","Balete","Banga","Batan","Buruanga","Kalibo","Lezo","Libacao","Madalag","Makato","Malinao","New Washington","Numancia","Tangalan","Ibajay","Nabas"],
    "Albay" => ["Bacacay","Camalig","Daraga","Guinobatan","Legazpi City","Ligao City","Malilipot","Manito","Oas","Pio Duran","Polangui","Santo Domingo","Tabaco City","Tiwi","Viga"],
  "Antique" => ["San Jose de Buenavista","Sibalom","Hamtic","Tibiao","San Remigio","Patnongon","Anini-y","Bugasong","Valderrama","Laua-an","Culasi","Sebaste","Pandan","Libertad","Banga","Madalag","San Pedro","Bato"],
    "Apayao" => ["Calanasan","Conner","Flora","Kabugao","Luna","Pudtol","Santa Marcela"],
    "Aurora" => ["Baler","Casiguran","Dilasag","Dinalungan","Dipaculao","Dingalan","Maria Aurora","San Luis"],
    "Basilan" => ["Isabela City","Lamitan City","Maluso","Sumisip","Lantawan","Tipo-Tipo","Haji Mohammad Ajul","Tabuan-Lasa","Tuburan","Akbar","Hadji Panglima Tahil"],
    "Bataan" => ["Balanga City","Abucay","Bagac","Dinalupihan","Hermosa","Limay","Mariveles","Morong","Orani","Orion","Pilar"],
    "Batanes" => ["Basco","Itbayat","Ivana","Mahatao","Sabtang","Uyugan"],
    "Batangas" => ["Batangas City","Lipa City","Tanauan City","Lemery","Calaca","Cuenca","Ibaan","Laurel","Lian","Lobo","Mabini","Malvar","Mataasnakahoy","Nasugbu","Padre Garcia","Rosario","San Jose","San Juan","San Luis","San Nicolas","San Pascual","Santa Teresita","Santo Tomas","Taal","Talisay","Balayan","Balete","Bauan","Calatagan","Taysan","Tingloy","Agoncillo","Alitagtag"],
    "Benguet" => ["Baguio City","Atok","Bakun","Bokod","Buguias","Itogon","Kabayan","Kapangan","Kibungan","La Trinidad","Mankayan","Sablan","Tuba","Tublay"],
    "Biliran" => ["Naval","Almeria","Biliran","Caibiran","Kawayan","Maripipi"],

    "Bohol" => ["Tagbilaran City","Alburquerque","Alicia","Anda","Antequera","Baclayon","Balilihan","Batuan","Bien Unido","Bilar","Candijay","Carmen","Catigbian","Clarin","Corella","Cortes","Dagohoy","Danao","Dauis","Dimiao","Duero","Garcia Hernandez","Guindulman","Inabanga","Jagna","Loboc","Loon","Maribojoc","Panglao","Pilar","President Carlos P. Garcia","Sagbayan","San Isidro","San Miguel","Sevilla","Sierra Bullones","Sikatuna","Talibon","Trinidad","Tubigon","Ubay","Valencia"],

    "Bukidnon" => ["Malaybalay City","Valencia City","Baungon","Cabanglasan","Damulog","Dangcagan","Don Carlos","Impasugong","Kadingilan","Kalilangan","Kibawe","Kitanglad","Lantapan","Libona","Malitbog","Manolo Fortich","Maramag","Pangantucan","Quezon","San Fernando","Sumilao","Talakag"],

    "Bulacan" => ["Malolos","Meycauayan","San Jose del Monte","Angat","Balagtas","Baliuag","Bocaue","Bulacan","Calumpit","Doña Remedios Trinidad","Guiguinto","Hagonoy","Marilao","Norzagaray","Obando","Pandi","Plaridel","Pulilan","San Ildefonso","San Miguel","San Rafael","Santa Maria"],

    "Cagayan" => ["Tuguegarao City","Abulug","Alcala","Allacapan","Amulung","Aparri","Baggao","Ballesteros","Buguey","Calayan","Camalaniugan","Claveria","Enrile","Gattaran","Gonzaga","Iguig","Lal-lo","Lasam","Pamplona","Peñablanca","Piat","Rizal","Sanchez Mira","Santa Ana","Santa Praxedes","Santa Teresita","Solana"],

    "Camarines Norte" => ["Daet","Basud","Capalonga","Jose Panganiban","Labo","Mercedes","Paracale","San Lorenzo Ruiz","San Vicente","Santa Elena","Talisay","Vinzons"],

    "Camarines Sur" => ["Naga City","Iriga City","Calabanga","Sipocot","Baao","Balatan","Bato","Bulan","Buhi","Bula","Cabusao","Caramoan","Del Gallego","Gainza","Garchitorena","Goa","Libmanan","Lupi","Magarao","Milaor","Minalabac","Nabua","Pasacao","Pili","Presentacion","Ragay","Sagñay","San Fernando","San Jose","Siruma","Tigaon","Tinambac"],

    "Camiguin" => ["Mambajao","Catarman","Mahinog","Sagay","Guinsiliban"],

    "Capiz" => ["Roxas City","Panay","Pontevedra","Cuartero","Dao","Dumalag","Dumarao","Ivisan","Jamindan","Ma-ayon","Mambusao","Panitan","President Roxas","Roxas","Sapian","Sigma","Tapaz"],
    "Catanduanes" => ["Virac","Bagamanoc","Baras","Bato","Caramoran","Gigmoto","Panganiban","San Andres","San Miguel","Viga"],
    "Cavite" => ["Cavite City","Bacoor","Imus","Dasmariñas","Tagaytay","Trece Martires","General Trias","Alfonso","Amadeo","Bailen","Carmona","Gen. Mariano Alvarez","Indang","Magallanes","Maragondon","Mendez","Naic","Tanza","Ternate","Silang"],
    "Cebu" => ["Cebu City","Mandaue","Lapu-Lapu","Naga City","Talisay","Toledo","Danao","Carcar","Alcoy","Argao","Asturias","Badian","Balamban","Barili","Boljoon","Carmen","Catmon","Compostela","Consolacion","Cordova","Dalaguete","Dumanjug","Ginatilan","Liloan","Madridejos","Malabuyoc","Medellin","Minglanilla","Moalboal","Oslob","Pilar","Pinamungahan","Ronda","Samboan","San Fernando","San Francisco","Santa Catalina","Sogod","Tabogon","Tabuelan","Tuburan","Tudela"],
    "Cotabato" => ["Kidapawan","M’lang","Magpet","Makilala","Antipas","Aleosan","Pikit","Midsayap","Libungan","Carmen","Pres. Roxas","Banisilan","Arakan","Tantangan","Tulunan"],

    
"Davao de Oro" => ["Compostela","Monkayo","Nabunturan","Maco","Maragusan","Laak","Mabini","Mawab","New Bataan","Montevista","Pantukan"],

"Davao del Norte" => ["Tagum","Panabo","Samal","Asuncion","Braulio E. Dujali","Carmen","Kapalong","New Corella","San Isidro"],

"Davao del Sur" => ["Digos City","Bansalan","Hagonoy","Kiblawan","Magsaysay","Malalag","Matanao","Padada","Santa Cruz","Sulop"],

"Davao Oriental" => ["Mati City","Baganga","Banaybanay","Boston","Caraga","Cateel","Governor Generoso","Lupon","Manay","San Isidro","Tarragona"],

    "Dinagat Islands" => ["San Jose","Cagdianao","Libjo","Loreto","Basilisa","Tubajon","Dinagat"],

    "Eastern Samar" => ["Borongan City","Arteche","Balangiga","Balangkayan","Dolores","General MacArthur","Giporlos","Guiuan","Hernani","Jipapad","Lawaan","Llorente","Maslog","Maydolong","Oras","Quinapondan","Salcedo","San Julian","Sulat","Taft","Bato","San Isidro"],

    "Guimaras" => ["Jordan","Nueva Valencia","San Lorenzo","Sibunag","Buenavista"],

    "Ifugao" => ["Aguinaldo","Alfonso Lista","Asipulo","Banaue","Hingyon","Hungduan","Kiangan","Lagawe","Lamut","Mayoyao","Tinoc"],

    "Ilocos Norte" => ["Laoag","Batac","Paoay","Vintar","Adams","Bacarra","Badoc","Bangui","Burgos","Carasi","Currimao","Dingras","Dumalneg","Marcos","Nueva Era","Pagudpud","Piddig","Pinili","San Nicolas","Sarrat","Solsona"],

    "Ilocos Sur" => ["Vigan","Candon","Santa","Tagudin","Alcala","Bantay","Burgos","Cabugao","Caoayan","Cervantes","Galimuyod","Gregorio del Pilar","Lidlidda","Magsingal","Nagbukel","Narvacan","Quirino","Salcedo","San Emilio","San Esteban","San Ildefonso","San Juan","San Vicente","Santa Catalina","Santa Cruz","Santa Lucia","Santa Maria","Santiago","Sigay","Sinait","Sugpon","Suyo","Valladolid"],

    "Iloilo" => ["Iloilo City","Passi","Santa Barbara","Dumangas","Ajuy","Alimodian","Anilao","Badiangan","Balasan","Banate","Barotac Nuevo","Barotac Viejo","Batad","Bingawan","Cabatuan","Carles","Concepcion","Dingle","Dueñas","Estancia","Guimbal","Igbaras","Janiuay","Lambunao","Leganes","Leon","Maasin","Miagao","Mina","New Lucena","Oton","Pavia","Pototan","San Dionisio","San Enrique","San Joaquin","San Miguel","Sara","Tubungan","Tigbauan","Zarraga"],
    "Isabela" => ["Ilagan","Cauayan","Santiago","Echague","Alicia","Angadanan","Cabagan","Cabatuan","Gamu","Jones","Luna","Mallig","Naguilian","Palanan","Quezon","Quirino","Ramon","Reina Mercedes","Roxas","San Agustin","San Guillermo","San Isidro","San Manuel","San Mateo","Santo Tomas","Tumauini"],

    "Kalinga" => ["Tabuk","Balbalan","Lubuagan","Pasil","Pinukpuk","Rizal","Tinglayan"],

    "Laguna" => ["Santa Rosa","San Pedro","Calamba","San Pablo","Biñan","Cabuyao","Alaminos","Bay","Cavinti","Famy","Kalayaan","Liliw","Los Baños","Luisiana","Lumban","Mabitac","Magdalena","Majayjay","Nagcarlan","Paete","Pagsanjan","Pakil","Pangil","Pila","Rizal","Santa Cruz","Santa Maria","Siniloan"],

    "Lanao del Norte" => ["Iligan City","Kapatagan","Lala","Maigo","Bacolod","Baloi","Kolambugan","Linamon","Magsaysay","Matungao","Munai","Nunungan","Pantao Ragat","Pantar","Poona Piagapo","Salvador","Sapad","Sultan Naga Dimaporo","Tagoloan","Tangcal","Tubod","Kauswagan"],

    "Lanao del Sur" => ["Marawi City","Bacolod-Kalawi","Balabagan","Balindong","Bayang","Binidayan","Buadiposo-Buntong","Bubong","Butig","Calanogas","Ditsaan-Ramain","Ganassi","Kapatagan","Lumba-Bayabao","Lumbaca-Unayan","Maguing","Madamba","Madalum","Marantao","Marogong","Masiao","Mulondo","Pagayawan","Piagapo","Picong","Pualas","Saguiaran","Sultan Dumalondong","Tagoloan","Tamparan","Taraka","Tubaran","Tugaya","Wao"],

    "La Union" => ["San Fernando","Agoo","Bacnotan","Caba","Bagulin","Bauang","Burgos","Luna","Naguilian","Pugo","Rosario","San Gabriel","San Juan","Santo Tomas","Santol","Sudipen","Tubao","Bangar","Balaoan"],

    "Leyte" => ["Ormoc City","Tacloban City","Baybay City","Maasin City","Abuyog","Alangalang","Bato","Burauen","Calubian","Capoocan","Carigara","Dagami","Dulag","Hilongos","Hindang","Inopacan","Isabel","Jaro","Javier","Julita","Kananga","La Paz","MacArthur","Mahaplag","Matag-ob","Matalom","Mayorga","Merida","Palo","Palompon","Pastrana","San Isidro","San Miguel","Santa Fe","Tabango","Tanauan","Tolosa","Tunga","Villaba"],

    "Maguindanao del Norte" => ["Datu Odin Sinsuat","Datu Blah T. Sinsuat","Parang","Barira","Buldon","Kabuntalan","Matanog","Northern Kabuntalan","Shariff Aguak"],

    "Maguindanao del Sur" => ["Datu Saudi-Ampatuan","Datu Unsay","Shariff Aguak","Talayan","Buluan","Datu Paglas","Mangudadatu","Pagagawan"],

    "Marinduque" => ["Boac","Buenavista","Gasan","Santa Cruz","Mogpog","Torrijos"],

    "Masbate" => ["Masbate City","Aroroy","Baleno","Balud","Batuan","Cataingan","Cawayan","Claveria","Dimasalang","Esperanza","Mandaon","Milagros","Mobo","Monreal","Palanas","Pio V. Corpuz","Placer","San Fernando","San Jacinto","Uson"],

    "Misamis Occidental" => ["Ozamiz City","Tangub City","Bonifacio","Clarin","Concepcion","Don Victoriano Chiongbian","Jimenez","Lopez Jaena","Oroquieta","Panaon","Plaridel","Sapang Dalaga","Sinacaban","Tudela","Aloran"],

    "Misamis Oriental" => ["Cagayan de Oro","Gingoog","El Salvador","Initao","Alubijid","Balingasag","Balingoan","Binuangan","Claveria","Gitagum","Jasaan","Kinoguitan","Lagonglong","Laguindingan","Libertad","Lugait","Magsaysay","Manticao","Medina","Naawan","Opol","Salay","Sugbongcogon","Tagoloan","Talisayan","Villanueva"],

    "Mountain Province" => ["Bontoc","Barlig","Bauko","Besao","Natonin","Paracelis","Sabangan","Sadanga","Sagada","Tinglayan"],

    "Negros Occidental" => ["Bacolod","Escalante","Himamaylan","Kabankalan","Cadiz","Sagay","Silay","Talisay","Victorias","San Carlos","Sipalay","La Carlota","Murcia","Enrique B. Magalona","Hinigaran","Isabela","Ilog","La Castellana","Manapla","Moises Padilla","Pulupandan","San Enrique","Toboso","Valladolid"],

    "Negros Oriental" => ["Dumaguete","Bais","Tanjay","Canlaon","Bayawan","Guihulngan","Amlan","Ayungon","Bacong","Basay","Dauin","Jimalalud","La Libertad","Mabinay","Pamplona","San Jose","Santa Catalina","Siaton","Sibulan","Valencia","Manjuyod","Tayasan","Bindoy"],

    "Northern Samar" => ["Allen","Biri","Bobon","Capul","Catarman","Catubig","Gamay","Lavezares","Lapinig","Laoang","Lope de Vega","Mondragon","Palapag","Pambujan","Rosario","San Antonio","San Isidro","San Jose","San Roque","Silvino Lobos","Victoria","Las Navas","Mapanas"],

    "Nueva Ecija" => ["Cabanatuan","Gapan","San Jose","Palayan","Muñoz","Aliaga","Bongabon","Cabiao","Carranglan","Cuyapo","Gabaldon","General Mamerto Natividad","Guimba","Jaen","Laur","Licab","Llanera","Lupao","Quezon","Rizal","San Antonio","San Isidro","San Leonardo","Science City of Muñoz","Talavera","Talugtug","Zaragoza"],

    "Nueva Vizcaya" => ["Alfonso Castañeda","Ambaguio","Aritao","Bagabag","Bayombong","Diadi","Dupax del Norte","Dupax del Sur","Kasibu","Kayapa","Quezon","Santa Fe","Solano","Villaverde","Bambang"],

    "Occidental Mindoro" => ["Mamburao","Abra de Ilog","Calintaan","Looc","Lubang","Paluan","Pandan","Sablayan","San Jose","Santa Cruz","Rizal"],

    "Oriental Mindoro" => ["Calapan","Baco","Bongabong","Bulalacao","Gloria","Mansalay","Naujan","Pinamalayan","Pola","Puerto Galera","Roxas","San Teodoro","Socorro","Victoria"],

    "Palawan" => ["Puerto Princesa","El Nido","Coron","Roxas","Aborlan","Agutaya","Araceli","Balabac","Busuanga","Cagayancillo","Culion","Cuyo","Dumaran","Kalayaan","Linapacan","Magsaysay","Narra","Quezon","Rizal","San Vicente","Sofronio Española","Taytay","Brooke’s Point","Bataraza"],

    "Pampanga" => ["San Fernando","Angeles","Mabalacat","Porac","Apalit","Floridablanca","Guagua","Lubao","Macabebe","Magalang","Minalin","San Simon","Santa Rita","Santo Tomas","Arayat","Bacolor","Candaba","Mexico","Masantol","San Luis"],

    "Pangasinan" => ["Lingayen","Dagupan","San Carlos","Alaminos","Agno","Aguilar","Alcala","Anda","Asingan","Balungao","Bani","Basista","Bautista","Bayambang","Bolinao","Bugallon","Burgos","Calasiao","Dasol","Infanta","Labrador","Laoac","Lingayen","Mabini","Malasiqui","Manaoag","Mangaldan","Mangatarem","Mapandan","Natividad","Pozorrubio","Rosales","San Fabian","San Jacinto","San Manuel","San Nicolas","San Quintin","Santa Barbara","Santa Maria","Santo Tomas","Sison","Sual","Tayug","Umingan","Urbiztondo","Villasis"],

    "Quezon" => ["Lucena","Tayabas","Sariaya","Candelaria","Alcala","Agdangan","Atimonan","Buenavista","Burdeos","Calauag","Catanauan","General Luna","General Nakar","Guinayangan","Gumaca","Infanta","Jomalig","Lopez","Lucban","Macalelon","Mauban","Mulanay","Padre Burgos","Pagbilao","Panukulan","Patnanungan","Perez","Pitogo","Plaridel","Polillo","Quezon","Real","Sampaloc","San Andres","San Antonio","San Francisco","San Narciso","Tagkawayan","Tiaong","Unisan"],

    "Quirino" => ["Cabarroguis","Diffun","Maddela","Nagtipunan","Saguday","Aglipay"],

    "Rizal" => ["Antipolo","Cainta","Teresa","Angono","Taytay","Binangonan","Cardona","Jalajala","Morong","Pililla","Rodriguez","San Mateo","Tanay","Baras"],

    "Samar" => ["Catbalogan","San Jorge","Almagro","Basey","Calbiga","Daram","Gandara","Hinabangan","Jiabong","Marabut","Matuguinao","Motiong","Pagsanghan","Paranas","Pinabacdao","San Jose de Buan","Santa Rita","Santo Niño","Tagapul-an","Talalora","Tarangnan","Villareal","Zumarraga"],

    "Sarangani" => ["Alabel","Glan","Kiamba","Maasim","Maitum","Malungon"],

    "Siquijor" => ["Siquijor","Larena","San Juan","Lazi","Maria","Enrique Villanueva"],

    "Sorsogon" => ["Sorsogon City","Barcelona","Bulan","Gubat","Irosin","Juban","Casiguran","Castilla","Donsol","Magallanes","Matnog","Prieto Diaz","Santa Magdalena","Bacon","Pilar"],

    "South Cotabato" => ["Koronadal City","Banga","General Santos","Tampakan","Norala","Surallah","Tantangan","T’boli","Lake Sebu","Sto. Niño","Polomolok"],

    "Southern Leyte" => ["Maasin","Anahawan","Bontoc","Hinunangan","Hinundayan","Libagon","Liloan","Pintuyan","San Francisco","San Juan","San Ricardo","Silago","Sogod","St. Bernard","Tomas Oppus"],

    "Sultan Kudarat" => ["Isulan","Lambayong","Palimbang","President Quirino","Bagumbayan","Esperanza","Kalamansig","Lebak","Lutayan","Sen. Ninoy Aquino","Tacurong City"],

    "Sulu" => ["Jolo","Kalingalan Caluang","Maimbung","Indanan","Hadji Panglima Tahil","Luuk","Parang","Patikul","Panglima Estino","Pata","Talipao","Tapul","Omar","Lugus","Panglima Sugala","Mapun","Sibutu","Tandu-Bas","Pandami"],

    "Surigao del Norte" => ["Surigao City","Alegria","Bacuag","Burgos","Claver","Dapa","Del Carmen","General Luna","Gigaquit","Mainit","Pilar","Placer","San Francisco","San Isidro","Socorro","Sison","Tagana-an"],

    "Surigao del Sur" => ["Tandag City","Barobo","Bayabas","Cagwait","Cantilan","Carmen","Carrascal","Cortes","Hinatuan","Lanuza","Lianga","Lingig","Marihatag","San Agustin","San Miguel","Tagbina","Tago"],

    "Tarlac" => ["Tarlac City","Victoria","Paniqui","Concepcion","Anao","Bamban","Camiling","Capas","Gerona","La Paz","Mayantoc","Moncada","Pura","Ramos","San Clemente","San Jose","Santa Ignacia"],

    "Tawi-Tawi" => ["Bongao","Mapun","Simunul","Tandubas","Sapa-Sapa","Languyan","Panglima Sugala","Sitangkai","Sibutu","South Ubian"],

    "Zambales" => ["Iba","Olongapo","Subic","San Antonio","Botolan","Cabangan","Candelaria","Castillejos","Masinloc","Palauig","San Felipe","San Marcelino","Santa Cruz","Sta. Rita","San Narciso"],

    "Zamboanga del Norte" => ["Dipolog","Dapitan","Katipunan","Manukan","Sindangan","Siayan","Rizal","Piñan","Polanco","Labason","Liloy","Sibuco","Mutia","Godod","Jose Dalman","Sergio Osmeña Sr.","Roxas","Baliguian","Tampilisan"],

    "Zamboanga del Sur" => ["Pagadian","Dumingag","Guipos","Midsalip","Aurora","Bayog","Dimataling","Dinas","Dumalinao","Josefina","Kumalarang","Lakewood","Margosatubig","Molave","Pitogo","Ramon Magsaysay","San Miguel","San Pablo","Siayan","Sirawai","Tabina","Tambulig","Tungawan"],


];

$province_distances = [
    "Abra" => 1200,
    "Agusan del Norte" => 1300,
    "Agusan del Sur" => 1400,
    "Aklan" => 450,
    "Albay" => 430,
    "Antique" => 500,
    "Apayao" => 1300,
    "Aurora" => 1000,
    "Basilan" => 1150,
    "Bataan" => 950,
    "Batanes" => 2100,
    "Batangas" => 880,
    "Benguet" => 970,
    "Biliran" => 210,
    "Bohol" => 75,
    "Bukidnon" => 620,
    "Bulacan" => 900,
    "Cagayan" => 1800,
    "Camarines Norte" => 430,
    "Camarines Sur" => 420,
    "Camiguin" => 700,
    "Capiz" => 210,
    "Catanduanes" => 450,
    "Cavite" => 900,
    "Cebu" => 0,
    "Cotabato" => 1200,
    "Davao de Oro" => 1300,
    "Davao del Norte" => 1250,
    "Davao del Sur" => 1280,
    "Davao Oriental" => 1400,
    "Dinagat Islands" => 1350,
    "Eastern Samar" => 360,
    "Guimaras" => 80,
    "Ifugao" => 1050,
    "Ilocos Norte" => 1550,
    "Ilocos Sur" => 1500,
    "Iloilo" => 200,
    "Isabela" => 1700,
    "Kalinga" => 1500,
    "La Union" => 1550,
    "Laguna" => 900,
    "Lanao del Norte" => 610,
    "Lanao del Sur" => 630,
    "Leyte" => 200,
    "Maguindanao del Norte" => 1220,
    "Maguindanao del Sur" => 1230,
    "Marinduque" => 880,
    "Masbate" => 400,
    "Misamis Occidental" => 560,
    "Misamis Oriental" => 580,
    "Mountain Province" => 1000,
    "Negros Occidental" => 250,
    "Negros Oriental" => 130,
    "Northern Samar" => 350,
    "Nueva Ecija" => 950,
    "Nueva Vizcaya" => 1450,
    "Occidental Mindoro" => 780,
    "Oriental Mindoro" => 820,
    "Palawan" => 620,
    "Pampanga" => 920,
    "Pangasinan" => 1000,
    "Quezon" => 900,
    "Quirino" => 1450,
    "Rizal" => 900,
    "Samar" => 360,
    "Sarangani" => 1100,
    "Siquijor" => 90,
    "Sorsogon" => 450,
    "South Cotabato" => 1150,
    "Southern Leyte" => 220,
    "Sultan Kudarat" => 1180,
    "Sulu" => 1150,
    "Surigao del Norte" => 800,
    "Surigao del Sur" => 850,
    "Tarlac" => 920,
    "Tawi-Tawi" => 1300,
    "Zambales" => 950,
    "Zamboanga del Norte" => 620,
    "Zamboanga del Sur" => 650,
];


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $zip = $_POST['zip'] ?? '';
    $birthday = $_POST['birthday'];
    $role = 'customer';

    $distance_km = $province_distances[$province] ?? 100;
    $avg_speed = 40; 
    $delivery_minutes = round(($distance_km / $avg_speed) * 60);

    // Calculate age
    $age = date_diff(date_create($birthday), date_create('today'))->y;

    if($password !== $confirm_password){
        $message = "Passwords do not match.";
    } else if(!isset($provinces[$province]) || !in_array($city, $provinces[$province])){
        $message = "Could not locate your city in the Philippines.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username,email,password_hash,role,street,city,province,zip,birthday,age,distance_km,delivery_minutes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
$stmt->bind_param("sssssssssiid",
    $username, $email, $hash, $role,
    $street, $city, $province, $zip,
    $birthday, $age, $distance_km, $delivery_minutes
);



        if($stmt->execute()){
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['birthday_discount'] = (date('m-d') == date('m-d', strtotime($birthday))) ? 10 : 0;

            header("Location: store.php");
            exit;
        } else {
            $message = "Username or email already exists.";
        }

        $stmt->close();
    }}
 // <-- Make sure this closes the POST block


 
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - PC Parts Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* Video background */
#bg-video { position: fixed; top:0; left:0; width:100%; height:100%; object-fit: cover; z-index:-1; pointer-events:none; }
body { margin:0; font-family: Arial, sans-serif; color:#fff; min-height:100vh; overflow-x:hidden; }

/* Header */
header { background: rgba(0,0,0,0.4); color:white; padding:20px 40px; display:flex; justify-content:space-between; align-items:center; position:relative; z-index:10; backdrop-filter: blur(5px);}
header a { color:white; text-decoration:none; margin-left:20px; font-weight:bold;}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Main Content */
.main-content { display:flex; justify-content:center; align-items:center; padding:60px 20px; min-height: calc(100vh - 120px); }
.register-card { background: rgba(0,0,0,0.5); padding:40px; border-radius:12px; box-shadow:0 4px 30px rgba(0,0,0,0.5); width:100%; max-width:400px; text-align:center; color:#fff; backdrop-filter: blur(10px);}
.register-card h2, .register-card p, .register-card input, .register-card select, .register-card button { color:#fff; }
form { display:flex; flex-direction:column; gap:15px; }

/* Inputs */
input, select { padding:12px; border:1px solid rgba(255,255,255,0.4); border-radius:6px; font-size:16px; background: rgba(255,255,255,0.15); color:#fff; outline:none; }
input::placeholder { color: rgba(255,255,255,0.85);}
input[type="date"] { color:#fff; }
input[type="date"]::-webkit-calendar-picker-indicator { filter:invert(1); cursor:pointer; }
input:-webkit-autofill { -webkit-text-fill-color:white !important; transition:background-color 9999s ease-in-out 0s; }

/* Button */
.btn { padding:14px; background: rgba(0, 0, 0, 0.7); color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:all 0.3s; }
.btn:hover { background: rgba(34,197,94,1); transform: translateY(-2px); box-shadow:0 5px 15px rgba(34,197,94,0.5); }

.error-msg { color: #f87171; margin-top:15px; font-weight:bold; font-size:14px; }
select {
    color: #000 !important;
    background: rgba(255,255,255,0.9) !important;
}

/* Make dropdown options readable */
select option {
    color: #000 !important;
    background: #fff !important;
}
/* Footer */
footer { background: rgba(0,0,0,0.4); color:white; text-align:center; padding:20px; position:fixed; bottom:0; width:100%; backdrop-filter: blur(5px); z-index:10;}
</style>
</head>
<body>

<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/login.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <a href="index.php" class="logo">Home</a>
        
    </nav>
</header>

<div class="main-content">
    <div class="register-card">
        <h2>Register</h2>
        <p>Join the PC Parts Hub community.</p>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

            <!-- Street -->
            <input type="text" name="street" placeholder="Street / Barangay" required value="<?php echo isset($_POST['street']) ? htmlspecialchars($_POST['street']) : ''; ?>">

            <!-- Province Dropdown -->
            <select name="province" id="province" required onchange="updateCities()">
                <option value="">Select Province</option>
                <?php foreach($provinces as $prov => $cities): ?>
                    <option value="<?php echo $prov; ?>" <?php echo (isset($_POST['province']) && $_POST['province']==$prov)?'selected':''; ?>><?php echo $prov; ?></option>
                <?php endforeach; ?>
            </select>

            <!-- City Dropdown -->
            <select name="city" id="city" required>
                <option value="">Select City/Municipality</option>
                <?php
                if(isset($_POST['province']) && isset($provinces[$_POST['province']])){
                    foreach($provinces[$_POST['province']] as $c){
                        $sel = (isset($_POST['city']) && $_POST['city']==$c)?'selected':'';
                        echo "<option value=\"$c\" $sel>$c</option>";
                    }
                }
                ?>
            </select>

            <input type="text" name="zip" placeholder="ZIP Code" value="<?php echo isset($_POST['zip']) ? htmlspecialchars($_POST['zip']) : ''; ?>">

            <input type="date" name="birthday" required value="<?php echo isset($_POST['birthday']) ? htmlspecialchars($_POST['birthday']) : ''; ?>">

            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <button type="submit" class="btn">Create Account</button>
        </form>

        <?php if($message): ?>
            <p class="error-msg"><?php echo $message; ?></p>
        <?php endif; ?>

        <p style="margin-top:20px; font-size:14px;">
            Already have an account? <a href="login.php" style="color:#22c55e; font-weight:bold;">Login</a>
        </p>
    </div>
</div>

<script>
// Update city dropdown when province changes
const provinces = <?php echo json_encode($provinces); ?>;
function updateCities(){
    const provSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const selectedProv = provSelect.value;

    citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
    if(selectedProv && provinces[selectedProv]){
        provinces[selectedProv].forEach(c=>{
            const opt = document.createElement('option');
            opt.value = c;
            opt.text = c;
            citySelect.appendChild(opt);
        });
    }
}
</script>

<footer>
    <p>&copy; <?php echo date("Y"); ?> PC Parts Hub</p>
</footer>

</body>
</html>
