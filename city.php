<?php
/*
Plugin Name: Города и Регионы
Plugin URI: https://github.com/WebProWWW/wp-plugin-city
Description: Определяет город поддомена. Внедряет "popup" уточнения города и "popup" выбора города
Author: WebPRO
Author URI: https://webprowww.github.io
Version: 1.0.1
*/

use plugins\city\models\City;

require_once __DIR__ . '/models/City.php';
require_once __DIR__ . '/models/Region.php';

global $city;

add_action('init', function () {
	global $city;
	$city = City::findDomainCity();
	if ($city->alias === 'index') {
		$city->isConfirmed = true;
	} elseif ( isset( $_COOKIE['city-confirmed'] ) and $_COOKIE['city-confirmed'] === $city->alias ) {
		$city->isConfirmed = true;
	} else {
		$city->isConfirmed = false;
		setcookie( 'city-confirmed', $city->alias, time() + ( 60 * 60 * 24 * 30 * 12 ), '/', City::rootDomain() );
	}
});

add_action('wp_footer', function () {
	require __DIR__ . '/views/modals.php';
});

add_action('wp_enqueue_scripts', function () {
	$v = WP_DEBUG ? 'r' . time() : '1';
	wp_enqueue_script('jquery');
	wp_enqueue_style('fancybox', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css');
	wp_enqueue_script('fancybox', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js');
	wp_enqueue_script('region', plugins_url('/public/js/region.js', __FILE__), [], $v, true);
});

register_activation_hook(__FILE__, function () {
	global $wpdb;
	$wpdb->query("SET foreign_key_checks=0");

	$tableRegion = $wpdb->prefix . 'region';
	$wpdb->query("DROP TABLE IF EXISTS `$tableRegion`;");
	$wpdb->query("CREATE TABLE `$tableRegion` (
		`id` int NOT NULL AUTO_INCREMENT,
		`code` int NOT NULL,
		`name` varchar(255) DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `idx-region-code` (`code`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
	$wpdb->query("INSERT INTO `$tableRegion` (id, code, name) VALUES (1, 1,'Республика Адыгея'),(2, 2,'Республика Башкортостан'),(3, 3,'Республика Бурятия'),(4, 4,'Республика Алтай'),(5, 5,'Республика Дагестан'),(6, 6,'Республика Ингушетия'),(7, 7,'Кабардино-Балкарская Республика'),(8, 8,'Республика Калмыкия'),(9, 9,'Карачаево-Черкесская Республика'),(10, 10,'Республика Карелия'),(11, 11,'Республика Коми'),(12, 12,'Республика Марий Эл'),(13, 13,'Республика Мордовия'),(14, 14,'Республика Саха (Якутия)'),(15, 15,'Республика Северная Осетия-Алания'),(16, 16,'Республика Татарстан'),(17, 17,'Республика Тыва'),(18, 18,'Удмуртская Республика'),(19, 19,'Республика Хакасия'),(20, 20,'Чеченская Республика'),(21, 21,'Чувашская Республика - Чувашия'),(22, 22,'Алтайский край'),(23, 23,'Краснодарский край'),(24, 24,'Красноярский край'),(25, 25,'Приморский край'),(26, 26,'Ставропольский край'),(27, 27,'Хабаровский край и Еврейская автономная область'),(28, 28,'Амурская область'),(29, 29,'Архангельская область и Ненецкий АО'),(30, 30,'Астраханская область'),(31, 31,'Белгородская область'),(32, 32,'Брянская область'),(33, 33,'Владимирская область'),(34, 34,'Волгоградская область'),(35, 35,'Вологодская область'),(36, 36,'Воронежская область'),(37, 37,'Ивановская область'),(38, 38,'Иркутская область'),(39, 39,'Калининградская область'),(40, 40,'Калужская область'),(41, 41,'Камчатский край и Чукотский АО'),(42, 42,'Кемеровская область - Кузбасс'),(43, 43,'Кировская область'),(44, 44,'Костромская область'),(45, 45,'Курганская область'),(46, 46,'Курская область'),(47, 47,'Ленинградская область'),(48, 48,'Липецкая область'),(49, 49,'Магаданская область'),(50, 50,'Московская область'),(51, 51,'Мурманская область'),(52, 52,'Нижегородская область'),(53, 53,'Новгородская область'),(54, 54,'Новосибирская область'),(55, 55,'Омская область'),(56, 56,'Оренбургская область'),(57, 57,'Орловская область'),(58, 58,'Пензенская область'),(59, 59,'Пермский край'),(60, 60,'Псковская область'),(61, 61,'Ростовская область'),(62, 62,'Рязанская область'),(63, 63,'Самарская область'),(64, 64,'Саратовская область'),(65, 65,'Сахалинская область'),(66, 66,'Свердловская область'),(67, 67,'Смоленская область'),(68, 68,'Тамбовская область'),(69, 69,'Тверская область'),(70, 70,'Томская область'),(71, 71,'Тульская область'),(72, 72,'Тюменская область'),(73, 73,'Ульяновская область'),(74, 74,'Челябинская область'),(75, 75,'Забайкальский край'),(76, 76,'Ярославская область'),(77, 77,'Москва'),(78, 78,'Санкт-Петербург'),(82, 82,'Республика Крым'),(86, 86,'Ханты-Мансийский АО-Югра'),(87, 87,'Чукотский АО'),(89, 89,'Ямало-Ненецкий АО'),(92, 92,'Севастополь'),(99, 99,'Все регионы');");

	$tableCity = $wpdb->prefix . 'city';
	$wpdb->query("DROP TABLE IF EXISTS `$tableCity`;");
	$wpdb->query("CREATE TABLE `$tableCity` (
		`id` int NOT NULL AUTO_INCREMENT,
		`alias` varchar(255) NOT NULL,
		`name` varchar(255) NOT NULL,
		`region_id` int DEFAULT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `alias_UNIQUE` (`alias`),
		KEY `fk-city-region_idx` (`region_id`),
		CONSTRAINT `fk-city-region` FOREIGN KEY (`region_id`) REFERENCES `$tableRegion` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
	$wpdb->query("INSERT INTO `$tableCity` (alias, name, region_id) VALUES ('abaza','Абаза',19),('abakan','Абакан',19),('abdulino','Абдулино',56),('abinsk','Абинск',23),('agidel','Агидель',2),('agryz','Агрыз',16),('adygeysk','Адыгейск',1),('aznakaevo','Азнакаево',16),('azov','Азов',61),('ak-dovurak','Ак-Довурак',17),('aksay','Аксай',61),('alagir','Алагир',15),('alapaevsk','Алапаевск',66),('alatyr','Алатырь',21),('aldan','Алдан',14),('aleysk','Алейск',4),('aleksandrov','Александров',33),('aleksandrovsk','Александровск',59),('aleksandrovsk-sahalinskiy','Александровск-Сахалинский',65),('alekseevka','Алексеевка',31),('aleksin','Алексин',71),('alzamay','Алзамай',38),('alupka','Алупка',82),('alushta','Алушта',82),('almetevsk','Альметьевск',16),('amursk','Амурск',27),('anadyr','Анадырь',41),('anapa','Анапа',23),('angarsk','Ангарск',38),('andreapol','Андреаполь',69),('anzhero-sudzhensk','Анжеро-Судженск',42),('aniva','Анива',65),('apatity','Апатиты',51),('aprelevka','Апрелевка',50),('apsheronsk','Апшеронск',23),('aramil','Арамиль',66),('argun','Аргун',20),('ardatov','Ардатов',13),('ardon','Ардон',15),('arzamas','Арзамас',52),('arkadak','Аркадак',64),('armavir','Армавир',23),('armyansk','Армянск',82),('arsenev','Арсеньев',25),('arsk','Арск',16),('artem','Артём',25),('artemovsk','Артёмовск',24),('artemovskiy','Артёмовский',66),('arhangelsk','Архангельск',29),('asbest','Асбест',66),('asino','Асино',70),('astrahan','Астрахань',30),('atkarsk','Аткарск',64),('ahtubinsk','Ахтубинск',30),('achinsk','Ачинск',24),('asha','Аша',74),('babaevo','Бабаево',35),('babushkin','Бабушкин',3),('bavly','Бавлы',16),('bagrationovsk','Багратионовск',39),('baykalsk','Байкальск',38),('baymak','Баймак',2),('bakal','Бакал',74),('baksan','Баксан',7),('balabanovo','Балабаново',40),('balakovo','Балаково',64),('balahna','Балахна',52),('balashiha','Балашиха',50),('balashov','Балашов',64),('baley','Балей',75),('baltiysk','Балтийск',39),('barabinsk','Барабинск',54),('barnaul','Барнаул',4),('barysh','Барыш',73),('bataysk','Батайск',61),('bahchisaray','Бахчисарай',82),('bezhetsk','Бежецк',69),('belaya-kalitva','Белая Калитва',61),('belaya-holunitsa','Белая Холуница',43),('belgorod','Белгород',31),('belebey','Белебей',2),('belev','Белёв',71),('belinskiy','Белинский',58),('belovo','Белово',42),('belogorsk-amurskaya','Белогорск (Амурская область)',28),('belogorsk-krym','Белогорск (Крым)',82),('belozersk','Белозерск',35),('belokuriha','Белокуриха',4),('belomorsk','Беломорск',10),('beloozerskiy','Белоозёрский',50),('beloretsk','Белорецк',2),('belorechensk','Белореченск',23),('belousovo','Белоусово',40),('beloyarskiy','Белоярский',86),('belyy','Белый',69),('berdsk','Бердск',54),('berezniki','Березники',59),('berezovskiy-kemerovskaya','Берёзовский (Кемеровская область)',42),('berezovskiy-sverdlovskaya','Берёзовский (Свердловская область)',66),('beslan','Беслан',15),('biysk','Бийск',4),('bikin','Бикин',27),('bilibino','Билибино',41),('birobidzhan','Биробиджан',27),('birsk','Бирск',2),('biryusinsk','Бирюсинск',38),('biryuch','Бирюч',31),('blagoveschensk-bashkortostan','Благовещенск (Республика Башкортостан)',2),('blagoveschensk-amurskaya','Благовещенск (Амурская область)',28),('blagodarnyy','Благодарный',26),('bobrov','Бобров',36),('bogdanovich','Богданович',66),('bogoroditsk','Богородицк',71),('bogorodsk','Богородск',52),('bogotol','Боготол',24),('boguchar','Богучар',36),('bodaybo','Бодайбо',38),('boksitogorsk','Бокситогорск',47),('bolgar','Болгар',16),('bologoe','Бологое',69),('bolotnoe','Болотное',54),('bolohovo','Болохово',71),('bolhov','Болхов',57),('bolshoy-kamen','Большой Камень',25),('bor','Бор',52),('borzya','Борзя',75),('borisoglebsk','Борисоглебск',36),('borovichi','Боровичи',53),('borovsk','Боровск',40),('borodino','Бородино',24),('bratsk','Братск',38),('bronnitsy','Бронницы',50),('bryansk','Брянск',32),('bugulma','Бугульма',16),('buguruslan','Бугуруслан',56),('budennovsk','Будённовск',26),('buzuluk','Бузулук',56),('buinsk','Буинск',16),('buy','Буй',44),('buynaksk','Буйнакск',5),('buturlinovka','Бутурлиновка',36),('valday','Валдай',53),('valuyki','Валуйки',31),('velizh','Велиж',67),('velikie-luki','Великие Луки',60),('velikiy-novgorod','Великий Новгород',53),('velikiy-ustyug','Великий Устюг',35),('velsk','Вельск',29),('venev','Венёв',71),('vereschagino','Верещагино',59),('vereya','Верея',50),('verhneuralsk','Верхнеуральск',74),('verhniy-tagil','Верхний Тагил',66),('verhniy-ufaley','Верхний Уфалей',74),('verhnyaya-pyshma','Верхняя Пышма',66),('verhnyaya-salda','Верхняя Салда',66),('verhnyaya-tura','Верхняя Тура',66),('verhoture','Верхотурье',66),('verhoyansk','Верхоянск',14),('vesegonsk','Весьегонск',69),('vetluga','Ветлуга',52),('vidnoe','Видное',50),('vilyuysk','Вилюйск',14),('vilyuchinsk','Вилючинск',41),('vihorevka','Вихоревка',38),('vichuga','Вичуга',37),('vladivostok','Владивосток',25),('vladikavkaz','Владикавказ',15),('vladimir','Владимир',33),('volgograd','Волгоград',34),('volgodonsk','Волгодонск',61),('volgorechensk','Волгореченск',44),('volzhsk','Волжск',12),('volzhskiy','Волжский',34),('vologda','Вологда',35),('volodarsk','Володарск',52),('volokolamsk','Волоколамск',50),('volosovo','Волосово',47),('volhov','Волхов',47),('volchansk','Волчанск',66),('volsk','Вольск',64),('vorkuta','Воркута',11),('voronezh','Воронеж',36),('vorsma','Ворсма',52),('voskresensk','Воскресенск',50),('votkinsk','Воткинск',18),('vsevolozhsk','Всеволожск',47),('vuktyl','Вуктыл',11),('vyborg','Выборг',47),('vyksa','Выкса',52),('vysokovsk','Высоковск',50),('vysotsk','Высоцк',47),('vytegra','Вытегра',35),('vyshniy-volochek','Вышний Волочёк',69),('vyazemskiy','Вяземский',27),('vyazniki','Вязники',33),('vyazma','Вязьма',67),('vyatskie-polyany','Вятские Поляны',43),('gavrilov-posad','Гаврилов Посад',37),('gavrilov-yam','Гаврилов-Ям',76),('gagarin','Гагарин',67),('gadzhievo','Гаджиево',51),('gay','Гай',56),('galich','Галич',44),('gatchina','Гатчина',47),('gvardeysk','Гвардейск',39),('gdov','Гдов',60),('gelendzhik','Геленджик',23),('georgievsk','Георгиевск',26),('glazov','Глазов',18),('golitsyno','Голицыно',50),('gorbatov','Горбатов',52),('gorno-altaysk','Горно-Алтайск',4),('gornozavodsk','Горнозаводск',59),('gornyak','Горняк',4),('gorodets','Городец',52),('gorodische','Городище',58),('gorodovikovsk','Городовиковск',8),('gorohovets','Гороховец',33),('goryachiy-klyuch','Горячий Ключ',23),('grayvoron','Грайворон',31),('gremyachinsk','Гремячинск',59),('groznyy','Грозный',20),('gryazi','Грязи',48),('gryazovets','Грязовец',35),('gubaha','Губаха',59),('gubkin','Губкин',31),('gubkinskiy','Губкинский',89),('gudermes','Гудермес',20),('gukovo','Гуково',61),('gulkevichi','Гулькевичи',23),('gurevsk-kaliningradskaya','Гурьевск (Калининградская область)',39),('gurevsk-kemerovskaya','Гурьевск (Кемеровская область)',42),('gusev','Гусев',39),('gusinoozersk','Гусиноозёрск',3),('gus-hrustalnyy','Гусь-Хрустальный',33),('davlekanovo','Давлеканово',2),('dagestanskie-ogni','Дагестанские Огни',5),('dalmatovo','Далматово',45),('dalnegorsk','Дальнегорск',25),('dalnerechensk','Дальнереченск',25),('danilov','Данилов',76),('dankov','Данков',48),('degtyarsk','Дегтярск',66),('dedovsk','Дедовск',50),('demidov','Демидов',67),('derbent','Дербент',5),('desnogorsk','Десногорск',67),('dzhankoy','Джанкой',82),('dzerzhinsk','Дзержинск',52),('dzerzhinskiy','Дзержинский',50),('divnogorsk','Дивногорск',24),('digora','Дигора',15),('dimitrovgrad','Димитровград',73),('dmitriev','Дмитриев',46),('dmitrov','Дмитров',50),('dmitrovsk','Дмитровск',57),('dno','Дно',60),('dobryanka','Добрянка',59),('dolgoprudnyy','Долгопрудный',50),('dolinsk','Долинск',65),('domodedovo','Домодедово',50),('donetsk','Донецк',61),('donskoy','Донской',71),('dorogobuzh','Дорогобуж',67),('drezna','Дрезна',50),('dubna','Дубна',50),('dubovka','Дубовка',34),('dudinka','Дудинка',24),('duhovschina','Духовщина',67),('dyurtyuli','Дюртюли',2),('dyatkovo','Дятьково',32),('evpatoriya','Евпатория',82),('egorevsk','Егорьевск',50),('eysk','Ейск',23),('ekaterinburg','Екатеринбург',66),('elabuga','Елабуга',16),('elets','Елец',48),('elizovo','Елизово',41),('elnya','Ельня',67),('emanzhelinsk','Еманжелинск',74),('emva','Емва',11),('eniseysk','Енисейск',24),('ermolino','Ермолино',40),('ershov','Ершов',64),('essentuki','Ессентуки',26),('efremov','Ефремов',71),('zheleznovodsk','Железноводск',26),('zheleznogorsk-krasnoyarskij','Железногорск (Красноярский край)',24),('zheleznogorsk-kurskaya','Железногорск (Курская область)',46),('zheleznogorsk-ilimskiy','Железногорск-Илимский',38),('zherdevka','Жердевка',68),('zhigulevsk','Жигулёвск',63),('zhizdra','Жиздра',40),('zhirnovsk','Жирновск',34),('zhukov','Жуков',40),('zhukovka','Жуковка',32),('zhukovskiy','Жуковский',50),('zavitinsk','Завитинск',28),('zavodoukovsk','Заводоуковск',72),('zavolzhsk','Заволжск',37),('zavolzhe','Заволжье',52),('zadonsk','Задонск',48),('zainsk','Заинск',16),('zakamensk','Закаменск',3),('zaozernyy','Заозёрный',24),('zaozersk','Заозёрск',51),('zapadnaya-dvina','Западная Двина',69),('zapolyarnyy','Заполярный',51),('zaraysk','Зарайск',50),('zarechnyy-penzenskaya','Заречный (Пензенская область)',58),('zarechnyy-sverdlovskaya','Заречный (Свердловская область)',66),('zarinsk','Заринск',4),('zvenigovo','Звенигово',12),('zvenigorod','Звенигород',50),('zverevo','Зверево',61),('zelenogorsk','Зеленогорск',24),('zelenogradsk','Зеленоградск',39),('zelenodolsk','Зеленодольск',16),('zelenokumsk','Зеленокумск',26),('zernograd','Зерноград',61),('zeya','Зея',28),('zima','Зима',38),('zlatoust','Златоуст',74),('zlynka','Злынка',32),('zmeinogorsk','Змеиногорск',4),('znamensk','Знаменск',30),('zubtsov','Зубцов',69),('zuevka','Зуевка',43),('ivangorod','Ивангород',47),('ivanovo','Иваново',37),('ivanteevka','Ивантеевка',50),('ivdel','Ивдель',66),('igarka','Игарка',24),('izhevsk','Ижевск',18),('izberbash','Избербаш',5),('izobilnyy','Изобильный',26),('ilanskiy','Иланский',24),('inza','Инза',73),('innopolis','Иннополис',16),('insar','Инсар',13),('inta','Инта',11),('ipatovo','Ипатово',26),('irbit','Ирбит',66),('irkutsk','Иркутск',38),('isilkul','Исилькуль',55),('iskitim','Искитим',54),('istra','Истра',50),('ishim','Ишим',72),('ishimbay','Ишимбай',2),('yoshkar-ola','Йошкар-Ола',12),('kadnikov','Кадников',35),('kazan','Казань',16),('kalach','Калач',36),('kalach-na-donu','Калач-на-Дону',34),('kalachinsk','Калачинск',55),('kaliningrad','Калининград',39),('kalininsk','Калининск',64),('kaltan','Калтан',42),('kaluga','Калуга',40),('kalyazin','Калязин',69),('kambarka','Камбарка',18),('kamenka','Каменка',58),('kamennogorsk','Каменногорск',47),('kamensk-uralskiy','Каменск-Уральский',66),('kamensk-shahtinskiy','Каменск-Шахтинский',61),('kamen-na-obi','Камень-на-Оби',4),('kameshkovo','Камешково',33),('kamyzyak','Камызяк',30),('kamyshin','Камышин',34),('kamyshlov','Камышлов',66),('kanash','Канаш',21),('kandalaksha','Кандалакша',51),('kansk','Канск',24),('karabanovo','Карабаново',33),('karabash','Карабаш',74),('karabulak','Карабулак',6),('karasuk','Карасук',54),('karachaevsk','Карачаевск',9),('karachev','Карачев',32),('kargat','Каргат',54),('kargopol','Каргополь',29),('karpinsk','Карпинск',66),('kartaly','Карталы',74),('kasimov','Касимов',62),('kasli','Касли',74),('kaspiysk','Каспийск',5),('katav-ivanovsk','Катав-Ивановск',74),('kataysk','Катайск',45),('kachkanar','Качканар',66),('kashin','Кашин',69),('kashira','Кашира',50),('kedrovyy','Кедровый',70),('kemerovo','Кемерово',42),('kem','Кемь',10),('kerch','Керчь',82),('kizel','Кизел',59),('kizilyurt','Кизилюрт',5),('kizlyar','Кизляр',5),('kimovsk','Кимовск',71),('kimry','Кимры',69),('kingisepp','Кингисепп',47),('kinel','Кинель',63),('kineshma','Кинешма',37),('kireevsk','Киреевск',71),('kirensk','Киренск',38),('kirzhach','Киржач',33),('kirillov','Кириллов',35),('kirishi','Кириши',47),('kirov-kaluzhskaya','Киров (Калужская область)',40),('kirov-kirovskaya','Киров (Кировская область)',43),('kirovgrad','Кировград',66),('kirovo-chepetsk','Кирово-Чепецк',43),('kirovsk-leningradskaya','Кировск (Ленинградская область)',47),('kirovsk-murmanskaya','Кировск (Мурманская область)',51),('kirs','Кирс',43),('kirsanov','Кирсанов',68),('kiselevsk','Киселёвск',42),('kislovodsk','Кисловодск',26),('klin','Клин',50),('klintsy','Клинцы',32),('knyaginino','Княгинино',52),('kovdor','Ковдор',51),('kovrov','Ковров',33),('kovylkino','Ковылкино',13),('kogalym','Когалым',86),('kodinsk','Кодинск',24),('kozelsk','Козельск',40),('kozlovka','Козловка',21),('kozmodemyansk','Козьмодемьянск',12),('kola','Кола',51),('kologriv','Кологрив',44),('kolomna','Коломна',50),('kolpashevo','Колпашево',70),('kolchugino','Кольчугино',33),('kommunar','Коммунар',47),('komsomolsk','Комсомольск',37),('komsomolsk-na-amure','Комсомольск-на-Амуре',27),('konakovo','Конаково',69),('kondopoga','Кондопога',10),('kondrovo','Кондрово',40),('konstantinovsk','Константиновск',61),('kopeysk','Копейск',74),('korablino','Кораблино',62),('korenovsk','Кореновск',23),('korkino','Коркино',74),('korolev','Королёв',50),('korocha','Короча',31),('korsakov','Корсаков',65),('koryazhma','Коряжма',29),('kosterevo','Костерёво',33),('kostomuksha','Костомукша',10),('kostroma','Кострома',44),('kotelniki','Котельники',50),('kotelnikovo','Котельниково',34),('kotelnich','Котельнич',43),('kotlas','Котлас',29),('kotovo','Котово',34),('kotovsk','Котовск',68),('kohma','Кохма',37),('krasavino','Красавино',35),('krasnoarmeysk-moskovskaya','Красноармейск (Московская область)',50),('krasnoarmeysk-saratovskaya','Красноармейск (Саратовская область)',64),('krasnovishersk','Красновишерск',59),('krasnogorsk','Красногорск',50),('krasnodar','Краснодар',23),('krasnozavodsk','Краснозаводск',50),('krasnoznamensk-kaliningradskaya','Краснознаменск (Калининградская область)',39),('krasnoznamensk-moskovskaya','Краснознаменск (Московская область)',50),('krasnokamensk','Краснокаменск',75),('krasnokamsk','Краснокамск',59),('krasnoperekopsk','Красноперекопск',82),('krasnoslobodsk-volgogradskaya','Краснослободск (Волгоградская область)',34),('krasnoslobodsk-mordoviya','Краснослободск (Республика Мордовия)',13),('krasnoturinsk','Краснотурьинск',66),('krasnouralsk','Красноуральск',66),('krasnoufimsk','Красноуфимск',66),('krasnoyarsk','Красноярск',24),('krasnyy-kut','Красный Кут',64),('krasnyy-sulin','Красный Сулин',61),('krasnyy-holm','Красный Холм',69),('kremenki','Кремёнки',40),('kropotkin','Кропоткин',23),('krymsk','Крымск',23),('kstovo','Кстово',52),('kubinka','Кубинка',50),('kuvandyk','Кувандык',56),('kuvshinovo','Кувшиново',69),('kudrovo','Кудрово',47),('kudymkar','Кудымкар',59),('kuznetsk','Кузнецк',58),('kuybyshev','Куйбышев',54),('kukmor','Кукмор',16),('kulebaki','Кулебаки',52),('kumertau','Кумертау',2),('kungur','Кунгур',59),('kupino','Купино',54),('kurgan','Курган',45),('kurganinsk','Курганинск',23),('kurilsk','Курильск',65),('kurlovo','Курлово',33),('kurovskoe','Куровское',50),('kursk','Курск',46),('kurtamysh','Куртамыш',45),('kurchaloy','Курчалой',20),('kurchatov','Курчатов',46),('kusa','Куса',74),('kushva','Кушва',66),('kyzyl','Кызыл',17),('kyshtym','Кыштым',74),('kyahta','Кяхта',3),('labinsk','Лабинск',23),('labytnangi','Лабытнанги',89),('lagan','Лагань',8),('ladushkin','Ладушкин',39),('laishevo','Лаишево',16),('lakinsk','Лакинск',33),('langepas','Лангепас',86),('lahdenpohya','Лахденпохья',10),('lebedyan','Лебедянь',48),('leninogorsk','Лениногорск',16),('leninsk','Ленинск',34),('leninsk-kuznetskiy','Ленинск-Кузнецкий',42),('lensk','Ленск',14),('lermontov','Лермонтов',26),('lesnoy','Лесной',66),('lesozavodsk','Лесозаводск',25),('lesosibirsk','Лесосибирск',24),('livny','Ливны',57),('likino-dulevo','Ликино-Дулёво',50),('lipetsk','Липецк',48),('lipki','Липки',71),('liski','Лиски',36),('lihoslavl','Лихославль',69),('lobnya','Лобня',50),('lodeynoe-pole','Лодейное Поле',47),('losino-petrovskiy','Лосино-Петровский',50),('luga','Луга',47),('luza','Луза',43),('lukoyanov','Лукоянов',52),('luhovitsy','Луховицы',50),('lyskovo','Лысково',52),('lysva','Лысьва',59),('lytkarino','Лыткарино',50),('lgov','Льгов',46),('lyuban','Любань',47),('lyubertsy','Люберцы',50),('lyubim','Любим',76),('lyudinovo','Людиново',40),('lyantor','Лянтор',86),('magadan','Магадан',49),('magas','Магас',6),('magnitogorsk','Магнитогорск',74),('maykop','Майкоп',1),('mayskiy','Майский',7),('makarov','Макаров',65),('makarev','Макарьев',44),('makushino','Макушино',45),('malaya-vishera','Малая Вишера',53),('malgobek','Малгобек',6),('malmyzh','Малмыж',43),('maloarhangelsk','Малоархангельск',57),('maloyaroslavets','Малоярославец',40),('mamadysh','Мамадыш',16),('mamonovo','Мамоново',39),('manturovo','Мантурово',44),('mariinsk','Мариинск',42),('mariinskiy-posad','Мариинский Посад',21),('marks','Маркс',64),('mahachkala','Махачкала',5),('mglin','Мглин',32),('megion','Мегион',86),('medvezhegorsk','Медвежьегорск',10),('mednogorsk','Медногорск',56),('medyn','Медынь',40),('mezhgore','Межгорье',2),('mezhdurechensk','Междуреченск',42),('mezen','Мезень',29),('melenki','Меленки',33),('meleuz','Мелеуз',2),('mendeleevsk','Менделеевск',16),('menzelinsk','Мензелинск',16),('meschovsk','Мещовск',40),('miass','Миасс',74),('mikun','Микунь',11),('millerovo','Миллерово',61),('mineralnye-vody','Минеральные Воды',26),('minusinsk','Минусинск',24),('minyar','Миньяр',74),('mirnyy-saxa','Мирный (Республика Саха (Якутия))',14),('mirnyy-arhangelskaya','Мирный (Архангельская область)',29),('mihaylov','Михайлов',62),('mihaylovka','Михайловка',34),('mihaylovsk-sverdlovskaya','Михайловск (Свердловская область)',66),('mihaylovsk-stavropolskij','Михайловск (Ставропольский край)',26),('michurinsk','Мичуринск',68),('mogocha','Могоча',75),('mozhaysk','Можайск',50),('mozhga','Можга',18),('mozdok','Моздок',15),('monchegorsk','Мончегорск',51),('morozovsk','Морозовск',61),('morshansk','Моршанск',68),('mosalsk','Мосальск',40),('moskva','Москва',77),('muravlenko','Муравленко',89),('murashi','Мураши',43),('murino','Мурино',47),('murmansk','Мурманск',51),('murom','Муром',33),('mtsensk','Мценск',57),('myski','Мыски',42),('mytischi','Мытищи',50),('myshkin','Мышкин',76),('naberezhnye-chelny','Набережные Челны',16),('navashino','Навашино',52),('navoloki','Наволоки',37),('nadym','Надым',89),('nazarovo','Назарово',24),('nazran','Назрань',6),('nazyvaevsk','Называевск',55),('nalchik','Нальчик',7),('narimanov','Нариманов',30),('naro-fominsk','Наро-Фоминск',50),('nartkala','Нарткала',7),('naryan-mar','Нарьян-Мар',29),('nahodka','Находка',25),('nevel','Невель',60),('nevelsk','Невельск',65),('nevinnomyssk','Невинномысск',26),('nevyansk','Невьянск',66),('nelidovo','Нелидово',69),('neman','Неман',39),('nerekhta','Нерехта',44),('nerchinsk','Нерчинск',75),('neryungri','Нерюнгри',14),('nesterov','Нестеров',39),('neftegorsk','Нефтегорск',63),('neftekamsk','Нефтекамск',2),('neftekumsk','Нефтекумск',26),('nefteyugansk','Нефтеюганск',86),('neya','Нея',44),('nizhnevartovsk','Нижневартовск',86),('nizhnekamsk','Нижнекамск',16),('nizhneudinsk','Нижнеудинск',38),('nizhnie-sergi','Нижние Серги',66),('nizhniy-lomov','Нижний Ломов',58),('nizhniy-novgorod','Нижний Новгород',52),('nizhniy-tagil','Нижний Тагил',66),('nizhnyaya-salda','Нижняя Салда',66),('nizhnyaya-tura','Нижняя Тура',66),('nikolaevsk','Николаевск',34),('nikolaevsk-na-amure','Николаевск-на-Амуре',27),('nikolsk-vologodskaya','Никольск (Вологодская область)',35),('nikolsk-penzenskaya','Никольск (Пензенская область)',58),('nikolskoe','Никольское',47),('novaya-ladoga','Новая Ладога',47),('novaya-lyalya','Новая Ляля',66),('novoaleksandrovsk','Новоалександровск',26),('novoaltaysk','Новоалтайск',4),('novoanninskiy','Новоаннинский',34),('novovoronezh','Нововоронеж',36),('novodvinsk','Новодвинск',29),('novozybkov','Новозыбков',32),('novokubansk','Новокубанск',23),('novokuznetsk','Новокузнецк',42),('novokuybyshevsk','Новокуйбышевск',63),('novomichurinsk','Новомичуринск',62),('novomoskovsk','Новомосковск',71),('novopavlovsk','Новопавловск',26),('novorzhev','Новоржев',60),('novorossiysk','Новороссийск',23),('novosibirsk','Новосибирск',54),('novosil','Новосиль',57),('novosokolniki','Новосокольники',60),('novotroitsk','Новотроицк',56),('novouzensk','Новоузенск',64),('novoulyanovsk','Новоульяновск',73),('novouralsk','Новоуральск',66),('novohopersk','Новохопёрск',36),('novocheboksarsk','Новочебоксарск',21),('novocherkassk','Новочеркасск',61),('novoshahtinsk','Новошахтинск',61),('novyy-oskol','Новый Оскол',31),('novyy-urengoy','Новый Уренгой',89),('noginsk','Ногинск',50),('nolinsk','Нолинск',43),('norilsk','Норильск',24),('noyabrsk','Ноябрьск',89),('nurlat','Нурлат',16),('nytva','Нытва',59),('nyurba','Нюрба',14),('nyagan','Нягань',86),('nyazepetrovsk','Нязепетровск',74),('nyandoma','Няндома',29),('obluche','Облучье',27),('obninsk','Обнинск',40),('oboyan','Обоянь',46),('ob','Обь',54),('odintsovo','Одинцово',50),('ozersk-kaliningradskaya','Озёрск (Калининградская область)',39),('ozersk-chelyabinskaya','Озёрск (Челябинская область)',74),('ozery','Озёры',50),('oktyabrsk','Октябрьск',63),('oktyabrskiy','Октябрьский',2),('okulovka','Окуловка',53),('olekminsk','Олёкминск',14),('olenegorsk','Оленегорск',51),('olonets','Олонец',10),('omsk','Омск',55),('omutninsk','Омутнинск',43),('onega','Онега',29),('opochka','Опочка',60),('orel','Орёл',57),('orenburg','Оренбург',56),('orekhovo-zuevo','Орехово-Зуево',50),('orlov','Орлов',43),('orsk','Орск',56),('osa','Оса',59),('osinniki','Осинники',42),('ostashkov','Осташков',69),('ostrov','Остров',60),('ostrovnoy','Островной',51),('ostrogozhsk','Острогожск',36),('otradnoe','Отрадное',47),('otradnyy','Отрадный',63),('oha','Оха',65),('ohansk','Оханск',59),('ocher','Очёр',59),('pavlovo','Павлово',52),('pavlovsk','Павловск',36),('pavlovskiy-posad','Павловский Посад',50),('pallasovka','Палласовка',34),('partizansk','Партизанск',25),('pevek','Певек',41),('penza','Пенза',58),('pervomaysk','Первомайск',52),('pervouralsk','Первоуральск',66),('perevoz','Перевоз',52),('peresvet','Пересвет',50),('pereslavl-zalesskiy','Переславль-Залесский',76),('perm','Пермь',59),('pestovo','Пестово',53),('petrov-val','Петров Вал',34),('petrovsk','Петровск',64),('petrovsk-zabaykalskiy','Петровск-Забайкальский',75),('petrozavodsk','Петрозаводск',10),('petropavlovsk-kamchatskiy','Петропавловск-Камчатский',41),('petuhovo','Петухово',45),('petushki','Петушки',33),('pechora','Печора',11),('pechory','Печоры',60),('pikalevo','Пикалёво',47),('pionerskiy','Пионерский',39),('pitkyaranta','Питкяранта',10),('plavsk','Плавск',71),('plast','Пласт',74),('ples','Плёс',37),('povorino','Поворино',36),('podolsk','Подольск',50),('podporozhe','Подпорожье',47),('pokachi','Покачи',86),('pokrov','Покров',33),('pokrovsk','Покровск',14),('polevskoy','Полевской',66),('polessk','Полесск',39),('polysaevo','Полысаево',42),('polyarnye-zori','Полярные Зори',51),('polyarnyy','Полярный',51),('poronaysk','Поронайск',65),('porhov','Порхов',60),('pohvistnevo','Похвистнево',63),('pochep','Почеп',32),('pochinok','Починок',67),('poshekhone','Пошехонье',76),('pravdinsk','Правдинск',39),('privolzhsk','Приволжск',37),('primorsk-kaliningradskaya','Приморск (Калининградская область)',39),('primorsk-leningradskaya','Приморск (Ленинградская область)',47),('primorsko-ahtarsk','Приморско-Ахтарск',23),('priozersk','Приозерск',47),('prokopevsk','Прокопьевск',42),('proletarsk','Пролетарск',61),('protvino','Протвино',50),('prohladnyy','Прохладный',7),('pskov','Псков',60),('pugachev','Пугачёв',64),('pudozh','Пудож',10),('pustoshka','Пустошка',60),('puchezh','Пучеж',37),('pushkino','Пушкино',50),('puschino','Пущино',50),('pytalovo','Пыталово',60),('pyt-yah','Пыть-Ях',86),('pyatigorsk','Пятигорск',26),('raduzhnyy-vladimirskaya','Радужный (Владимирская область)',33),('raduzhnyy-hanty-mansijskij','Радужный (Ханты-Мансийский АО)',86),('raychihinsk','Райчихинск',28),('ramenskoe','Раменское',50),('rasskazovo','Рассказово',68),('revda','Ревда',66),('rezh','Реж',66),('reutov','Реутов',50),('rzhev','Ржев',69),('rodniki','Родники',37),('roslavl','Рославль',67),('rossosh','Россошь',36),('rostov-na-donu','Ростов-на-Дону',61),('rostov','Ростов',76),('roshal','Рошаль',50),('rtischevo','Ртищево',64),('rubtsovsk','Рубцовск',4),('rudnya','Рудня',67),('ruza','Руза',50),('ruzaevka','Рузаевка',13),('rybinsk','Рыбинск',76),('rybnoe','Рыбное',62),('rylsk','Рыльск',46),('ryazhsk','Ряжск',62),('ryazan','Рязань',62),('saki','Саки',82),('salavat','Салават',2),('salair','Салаир',42),('salekhard','Салехард',89),('salsk','Сальск',61),('samara','Самара',63),('sankt-peterburg','Санкт-Петербург',78),('saransk','Саранск',13),('sarapul','Сарапул',18),('saratov','Саратов',64),('sarov','Саров',52),('sasovo','Сасово',62),('satka','Сатка',74),('safonovo','Сафоново',67),('sayanogorsk','Саяногорск',19),('sayansk','Саянск',38),('svetlogorsk','Светлогорск',39),('svetlograd','Светлоград',26),('svetlyy','Светлый',39),('svetogorsk','Светогорск',47),('svirsk','Свирск',38),('svobodnyy','Свободный',28),('sebezh','Себеж',60),('sevastopol','Севастополь',92),('severo-kurilsk','Северо-Курильск',65),('severobaykalsk','Северобайкальск',3),('severodvinsk','Северодвинск',29),('severomorsk','Североморск',51),('severouralsk','Североуральск',66),('seversk','Северск',70),('sevsk','Севск',32),('segezha','Сегежа',10),('seltso','Сельцо',32),('semenov','Семёнов',52),('semikarakorsk','Семикаракорск',61),('semiluki','Семилуки',36),('sengiley','Сенгилей',73),('serafimovich','Серафимович',34),('sergach','Сергач',52),('sergiev-posad','Сергиев Посад',50),('serdobsk','Сердобск',58),('serov','Серов',66),('serpuhov','Серпухов',50),('sertolovo','Сертолово',47),('sibay','Сибай',2),('sim','Сим',74),('simferopol','Симферополь',82),('skovorodino','Сковородино',28),('skopin','Скопин',62),('slavgorod','Славгород',4),('slavsk','Славск',39),('slavyansk-na-kubani','Славянск-на-Кубани',23),('slantsy','Сланцы',47),('slobodskoy','Слободской',43),('slyudyanka','Слюдянка',38),('smolensk','Смоленск',67),('snezhinsk','Снежинск',74),('snezhnogorsk','Снежногорск',51),('sobinka','Собинка',33),('sovetsk-kaliningradskaya','Советск (Калининградская область)',39),('sovetsk-kirovskaya','Советск (Кировская область)',43),('sovetsk-tulskaya','Советск (Тульская область)',71),('sovetskaya-gavan','Советская Гавань',27),('sovetskiy','Советский',86),('sokol','Сокол',35),('soligalich','Солигалич',44),('solikamsk','Соликамск',59),('solnechnogorsk','Солнечногорск',50),('sol-iletsk','Соль-Илецк',56),('solvychegodsk','Сольвычегодск',29),('soltsy','Сольцы',53),('sorochinsk','Сорочинск',56),('sorsk','Сорск',19),('sortavala','Сортавала',10),('sosenskiy','Сосенский',40),('sosnovka','Сосновка',43),('sosnovoborsk','Сосновоборск',24),('sosnovyy-bor','Сосновый Бор',47),('sosnogorsk','Сосногорск',11),('sochi','Сочи',23),('spas-demensk','Спас-Деменск',40),('spas-klepiki','Спас-Клепики',62),('spassk','Спасск',58),('spassk-dalniy','Спасск-Дальний',25),('spassk-ryazanskiy','Спасск-Рязанский',62),('srednekolymsk','Среднеколымск',14),('sredneuralsk','Среднеуральск',66),('sretensk','Сретенск',75),('stavropol','Ставрополь',26),('staraya-kupavna','Старая Купавна',50),('staraya-russa','Старая Русса',53),('staritsa','Старица',69),('starodub','Стародуб',32),('staryy-krym','Старый Крым',82),('staryy-oskol','Старый Оскол',31),('sterlitamak','Стерлитамак',2),('strezhevoy','Стрежевой',70),('stroitel','Строитель',31),('strunino','Струнино',33),('stupino','Ступино',50),('suvorov','Суворов',71),('sudak','Судак',82),('sudzha','Суджа',46),('sudogda','Судогда',33),('suzdal','Суздаль',33),('sunzha','Сунжа',6),('suoyarvi','Суоярви',10),('surazh','Сураж',32),('surgut','Сургут',86),('surovikino','Суровикино',34),('sursk','Сурск',58),('susuman','Сусуман',49),('suhinichi','Сухиничи',40),('suhoy-log','Сухой Лог',66),('syzran','Сызрань',63),('syktyvkar','Сыктывкар',11),('sysert','Сысерть',66),('sychevka','Сычёвка',67),('syasstroy','Сясьстрой',47),('tavda','Тавда',66),('taganrog','Таганрог',61),('tayga','Тайга',42),('tayshet','Тайшет',38),('taldom','Талдом',50),('talitsa','Талица',66),('tambov','Тамбов',68),('tara','Тара',55),('tarko-sale','Тарко-Сале',89),('tarusa','Таруса',40),('tatarsk','Татарск',54),('tashtagol','Таштагол',42),('tver','Тверь',69),('teberda','Теберда',9),('teykovo','Тейково',37),('temnikov','Темников',13),('temryuk','Темрюк',23),('terek','Терек',7),('tetyushi','Тетюши',16),('timashevsk','Тимашёвск',23),('tihvin','Тихвин',47),('tihoretsk','Тихорецк',23),('tobolsk','Тобольск',72),('toguchin','Тогучин',54),('tolyatti','Тольятти',63),('tomari','Томари',65),('tommot','Томмот',14),('tomsk','Томск',70),('topki','Топки',42),('torzhok','Торжок',69),('toropets','Торопец',69),('tosno','Тосно',47),('totma','Тотьма',35),('trekhgornyy','Трёхгорный',74),('troitsk','Троицк',74),('trubchevsk','Трубчевск',32),('tuapse','Туапсе',23),('tuymazy','Туймазы',2),('tula','Тула',71),('tulun','Тулун',38),('turan','Туран',17),('turinsk','Туринск',66),('tutaev','Тутаев',76),('tynda','Тында',28),('tyrnyauz','Тырныауз',7),('tyukalinsk','Тюкалинск',55),('tyumen','Тюмень',72),('uvarovo','Уварово',68),('uglegorsk','Углегорск',65),('uglich','Углич',76),('udachnyy','Удачный',14),('udomlya','Удомля',69),('uzhur','Ужур',24),('uzlovaya','Узловая',71),('ulan-ude','Улан-Удэ',3),('ulyanovsk','Ульяновск',73),('unecha','Унеча',32),('uray','Урай',86),('uren','Урень',52),('urzhum','Уржум',43),('urus-martan','Урус-Мартан',20),('uryupinsk','Урюпинск',34),('usinsk','Усинск',11),('usman','Усмань',48),('usole-sibirskoe','Усолье-Сибирское',38),('usole','Усолье',59),('ussuriysk','Уссурийск',25),('ust-dzheguta','Усть-Джегута',9),('ust-ilimsk','Усть-Илимск',38),('ust-katav','Усть-Катав',74),('ust-kut','Усть-Кут',38),('ust-labinsk','Усть-Лабинск',23),('ustyuzhna','Устюжна',35),('ufa','Уфа',2),('uhta','Ухта',11),('uchaly','Учалы',2),('uyar','Уяр',24),('fatezh','Фатеж',46),('feodosiya','Феодосия',82),('fokino-bryanskaya','Фокино (Брянская область)',32),('fokino-primorskij','Фокино (Приморский край)',25),('frolovo','Фролово',34),('fryazino','Фрязино',50),('furmanov','Фурманов',37),('habarovsk','Хабаровск',27),('hadyzhensk','Хадыженск',23),('hanty-mansiysk','Ханты-Мансийск',86),('harabali','Харабали',30),('harovsk','Харовск',35),('hasavyurt','Хасавюрт',5),('hvalynsk','Хвалынск',64),('hilok','Хилок',75),('himki','Химки',50),('holm','Холм',53),('holmsk','Холмск',65),('hotkovo','Хотьково',50),('tsivilsk','Цивильск',21),('tsimlyansk','Цимлянск',61),('tsiolkovskiy','Циолковский',28),('chadan','Чадан',17),('chaykovskiy','Чайковский',59),('chapaevsk','Чапаевск',63),('chaplygin','Чаплыгин',48),('chebarkul','Чебаркуль',74),('cheboksary','Чебоксары',21),('chegem','Чегем',7),('chekalin','Чекалин',71),('chelyabinsk','Челябинск',74),('cherdyn','Чердынь',59),('cheremhovo','Черемхово',38),('cherepanovo','Черепаново',54),('cherepovets','Череповец',35),('cherkessk','Черкесск',9),('chermoz','Чёрмоз',59),('chernogolovka','Черноголовка',50),('chernogorsk','Черногорск',19),('chernushka','Чернушка',59),('chernyahovsk','Черняховск',39),('chekhov','Чехов',50),('chistopol','Чистополь',16),('chita','Чита',75),('chkalovsk','Чкаловск',52),('chudovo','Чудово',53),('chulym','Чулым',54),('chusovoy','Чусовой',59),('chuhloma','Чухлома',44),('shagonar','Шагонар',17),('shadrinsk','Шадринск',45),('shali','Шали',20),('sharypovo','Шарыпово',24),('sharya','Шарья',44),('shatura','Шатура',50),('shahty','Шахты',61),('shahunya','Шахунья',52),('shatsk','Шацк',62),('shebekino','Шебекино',31),('shelekhov','Шелехов',38),('shenkursk','Шенкурск',29),('shilka','Шилка',75),('shimanovsk','Шимановск',28),('shihany','Шиханы',64),('shlisselburg','Шлиссельбург',47),('shumerlya','Шумерля',21),('shumiha','Шумиха',45),('shuya','Шуя',37),('schekino','Щёкино',71),('schelkino','Щёлкино',82),('schelkovo','Щёлково',50),('schigry','Щигры',46),('schuche','Щучье',45),('elektrogorsk','Электрогорск',50),('elektrostal','Электросталь',50),('elektrougli','Электроугли',50),('elista','Элиста',8),('engels','Энгельс',64),('ertil','Эртиль',36),('yugorsk','Югорск',86),('yuzha','Южа',37),('yuzhno-sahalinsk','Южно-Сахалинск',65),('yuzhno-suhokumsk','Южно-Сухокумск',5),('yuzhnouralsk','Южноуральск',74),('yurga','Юрга',42),('yurev-polskiy','Юрьев-Польский',33),('yurevets','Юрьевец',37),('yuryuzan','Юрюзань',74),('yuhnov','Юхнов',40),('yadrin','Ядрин',21),('yakutsk','Якутск',14),('yalta','Ялта',82),('yalutorovsk','Ялуторовск',72),('yanaul','Янаул',2),('yaransk','Яранск',43),('yarovoe','Яровое',4),('yaroslavl','Ярославль',76),('yartsevo','Ярцево',67),('yasnogorsk','Ясногорск',71),('yasnyy','Ясный',56),('yahroma','Яхрома',50);");

	$wpdb->query("SET foreign_key_checks=1");
});