<?php

function _http_fetch_page($queryParam) {
	$url = LDFF_SCRAPING_ROOT . LDFF_COMPETITION_PAGE . '/?action=preview&' . $queryParam;
	return file_get_contents($url);
}

/*
	Retrieves UIDs from a whole page of entries.
	Pagination starts with index 1.
*/
function http_fetch_uids($page = 1) {
	$entry_list = array();

	$data = _http_fetch_page('start=' . (($page - 1) * LDFF_SCRAPING_PAGE_SIZE));
	phpQuery::newDocumentHTML($data);

	foreach(pq('.preview a') as $entry_el) {
		$entry_list[] = str_replace('?action=preview&uid=', '', pq($entry_el)->attr('href'));
		/*$title = pq('i', $entry_el)->text();
		$entry = array(
			'uid' => str_replace('?action=preview&uid=', '', pq($entry_el)->attr('href')),
			'author' => substr(pq($entry_el)->text(), strlen($title)),
			'title' => pq('i', $entry_el)->text(),
			'picture' => pq('img', $entry_el)->attr('src')
		);*/
	}

	return $entry_list;
}

/*
	Retrieves the full info for an entry
	TODO Comments
*/
function http_fetch_entry($uid) {
	static $PLATFORM_KEYWORDS = array(
		'Windows' => ['windows', 'win32', 'win64', 'java'],
		'Linux' => ['linux', 'debian', 'ubuntu', 'java'],
		'OS X' => ['mac', 'osx', 'os/x', 'os x', 'java'],
		'Android' => ['android'],

		'Web (Flash)' => ['flash', 'swf'],
		'Web (HTML5)' => ['html'],
		'Web (Unity)' => ['unity'],
		'Web' => ['web']
	);

	$data = _http_fetch_page('uid=' . $uid);
	phpQuery::newDocumentHTML($data);

	// Figure out platforms
	$platforms = '';
	$platforms_text = strtolower(pq('.links li')->text());
	foreach ($PLATFORM_KEYWORDS as $platform_name => $keywords) {
		$found = false;
		foreach ($keywords as $keyword) {
			if (strpos($platforms_text, $keyword) !== false) {
				$found = true;
				break;
			}
		}

		if ($found) {
			if ($platforms != '') {
				$platforms .= ', ';
			}
			$platforms .= $platform_name;
			if (strpos($platform_name, 'Web') !== false) {
				break; // Don't add multiple web platforms (e.g. "Web (Unity)" + "Web")
			}
		}
	}
	if ($platforms == '') {
		$platforms = 'Unknown';
	}

	// TODO Fix for pages with embedded games
	$entry = array(
		'uid' => $uid,
		'author' => pq('#compo2 a strong')->text(),
		'title' => pq('#compo2 h2')->eq(0)->text(),
		'type' => (pq('#compo2 > div > i')->text() == 'Compo Entry') ? 'compo' : 'jam',
		'description' => pq('#compo2 p')->eq(1)->html(),
		'platforms' => $platforms,
		'picture' => str_replace('//', '/', pq('.shot-nav img')->attr('src'))
	);

	return $entry;
}

?>