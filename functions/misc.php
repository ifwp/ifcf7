<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_ifcf7_placeholder_value')){
    function _ifcf7_placeholder_value($tag = null, $fallback = ''){
		if(empty($tag->values)){
            return $fallback;
        }
        if(in_array($tag->basetype, ['date', 'email', 'file', 'number', 'password', 'tel', 'text', 'textarea', 'url'])){
            if(!$tag->has_option('placeholder') and !$tag->has_option('watermark')){
                return $fallback;
            }
            return (string) reset($tag->values);
        } elseif('select' === $tag->basetype){
            if(!$tag->has_option('first_as_label') and !$tag->has_option('include_blank')){
                return $fallback;
            }
            return (string) reset($tag->values);
        }
        return $fallback;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_ifcf7_sanitize_posted_attr')){
    function _ifcf7_sanitize_posted_attr($attr = ''){
		$posted_attr = isset($_POST[$attr]) ? $_POST[$attr] : '';
		$posted_nonce = isset($_POST[$attr . 'nonce']) ? $_POST[$attr . 'nonce'] : '';
		$nonce_valid = $posted_attr ? wp_verify_nonce($posted_nonce, 'ifcf7-setup-' . $attr . '_' . $posted_attr) : false;
		if($nonce_valid){
			return $posted_attr;
		}
		return '';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_ifcf7_setup_shortcode_attr')){
    function _ifcf7_setup_shortcode_attr($attr = '', $value = '', $out = []){
		$out[$attr] = $value;
		$out[$attr . '_nonce'] = wp_create_nonce('ifcf7-setup-' . $attr . '_' . $value);
		return $out;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_action')){
    function ifcf7_action($contact_form = null){
		$value = ifcf7_meta_pref('ifcf7_action', $contact_form);
		if(!$value){
			$value = 'contact_form';
		}
		return $value;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_add_data_option')){
    function ifcf7_add_data_option(...$args){
        return IFCF7\Data_Options::add_data_option(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_add_data_query')){
	function ifcf7_add_data_query(...$args){
        return IFCF7\Data_Options::add_data_query(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_add_data_term_query')){
	function ifcf7_add_data_term_query(...$args){
        return IFCF7\Data_Options::add_data_term_query(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_add_data_user_query')){
	function ifcf7_add_data_user_query(...$args){
        return IFCF7\Data_Options::add_data_user_query(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_add_dynamic_field')){
    function ifcf7_add_dynamic_field(...$args){
        return IFCF7\Dynamic_Fields::add_dynamic_field(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_additional_setting')){
    function ifcf7_additional_setting($name = '', $single = false, $contact_form = null){
		if(null === $contact_form){
			$contact_form = wpcf7_get_current_contact_form();
			if(null === $contact_form){
				return $single ? '' : [];
			}
		}
		return $single ? (string) $contact_form->pref($name) : $contact_form->additional_setting($name, false);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_contact_form')){
    function ifcf7_contact_form($contact_form = null){
		$current_contact_form = wpcf7_get_current_contact_form();
        if(empty($contact_form)){ // null, false, 0 and other PHP falsey values
            return $current_contact_form;
        }
        if($contact_form instanceof WPCF7_ContactForm){
            return $contact_form;
        }
        if(is_numeric($contact_form) or $contact_form instanceof WP_Post){
            $contact_form = wpcf7_contact_form($contact_form); // replace the current contact form
            if(!is_null($current_contact_form)){
                wpcf7_contact_form($current_contact_form->id()); // restore the current contact form
            }
            return $contact_form; // null or WPCF7_ContactForm
        }
        if(is_string($contact_form)){
            $contact_form = wpcf7_get_contact_form_by_title($contact_form); // replace the current contact form
            if(!is_null($current_contact_form)){
                wpcf7_contact_form($current_contact_form->id()); // restore the current contact form
            }
            return $contact_form; // null or WPCF7_ContactForm
        }
        return null;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_content_label_value')){
    function ifcf7_content_label_value($tag = null, $fallback = ''){
		if(in_array($tag->basetype, ['acceptance', 'textarea']) and !$tag->has_option('content_as_label')){
			return $fallback;
		}
		$content = $tag->content ? ifwp_remove_whitespaces($tag->content) : '';
		if(!$content){
			return $fallback;
		}
		return $content;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_fa_icon')){
	function ifcf7_fa_icon($tag = null, $position = 'left'){
		$style = '';
		if($tag->has_option('fab')){
			$style = 'fab';
		}
		if($tag->has_option('fad')){
			$style = 'fad';
		}
		if($tag->has_option('fal')){
			$style = 'fal';
		}
		if($tag->has_option('far')){
			$style = 'far';
		}
		if($tag->has_option('fas')){
			$style = 'fas';
		}
		if(!$style){
			return '';
		}
		if(!$tag->has_option('fa')){
			return '';
		}
		$fa = $tag->get_option('fa', 'class', true);
		if(false === strpos($fa, 'fa-')){
			$fa = 'fa-' . $fa;
		}
		if('left' === $position){
			$fa .= ' mr-2';
		} elseif('right' === $position){
			$fa .= ' ml-2';
		}
		return '<i class="' . $style . ' ' . $fa . '"></i>';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_format_value')){
    function ifcf7_format_value($value = '', $tag = null){
		if(!$tag){
			return $value;
		}
		if('email' === $tag->basetype and wpcf7_is_email($value)){
			return '<a href="mailto:' . $value . '" target="_blank">' . $value . '</a>';
		} elseif('tel' === $tag->basetype and wpcf7_is_tel($value)){
			return '<a href="tel:' . $value . '" target="_blank">' . $value . '</a>';
		} elseif('url' === $tag->basetype and wpcf7_is_url($value)){
			return '<a href="' . $value . '" target="_blank">' . $value . '</a>';
		} else {
			return $value;
		}
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/*if(!function_exists('ifcf7_get_contact_form')){
    function ifcf7_get_contact_form($id = 0){
        $current_form = wpcf7_get_current_contact_form();
		$contact_form = wpcf7_contact_form($id);
		if(null !== $current_form){
			if(null !== $contact_form and $current_form->id() === $contact_form->id()){
				// fix
			} else {
				wpcf7_contact_form($current_form->id()); // reset
			}
		}
		return $contact_form;
    }
}*/

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_get_current_submission')){
    function ifcf7_get_current_submission(){
        if($current = WPCF7_Submission::get_instance()){
			return $current;
		}
		return null;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_get_meta_data')){
    function ifcf7_get_meta_data($name = ''){
        return IFCF7\Helper::get_meta_data($name);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_get_posted_data')){
    function ifcf7_get_posted_data($name = ''){
        return IFCF7\Helper::get_posted_data($name);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_get_post_status')){
    function ifcf7_get_post_status($contact_form = null){
		$status = ifcf7_meta_pref('ifcf7_post_status', $contact_form);
		if(!$status){
			return 'publish';
		}
        $statuses = get_post_statuses();
        if(isset($statuses[$status])){
            return $status;
        }
        return 'publish';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_get_post_type')){
    function ifcf7_get_post_type($contact_form = null){
		$type = ifcf7_meta_pref('ifcf7_post_type', $contact_form);
		if(!$type){
			return 'ifcf7-submission';
		}
        if(!post_type_exists($type)){
            return 'ifcf7-submission';
        }
        return $type;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_has_data_options')){
    function ifcf7_has_data_options($tag = null){
		return ($tag->get_data_option() ? true : false);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_has_free_text')){
    function ifcf7_has_free_text($tag = null){
		return $tag->has_option('free_text');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_has_pipes')){
    function ifcf7_has_pipes($tag = null){
		if(WPCF7_USE_PIPE and $tag->pipes instanceof WPCF7_Pipes and !$tag->pipes->zero()){
        	$pipes = $tag->pipes->to_array();
			foreach($pipes as $pipe){
				if($pipe[0] !== $pipe[1]){
					return true;
					break;
				}
			}
        }
		return false;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_intl_countries')){
    function ifcf7_intl_countries(){
        return [ [ "Afghanistan (‫افغانستان‬‎)", "af", "93" ], [ "Albania (Shqipëri)", "al", "355" ], [ "Algeria (‫الجزائر‬‎)", "dz", "213" ], [ "American Samoa", "as", "1", 5, [ "684" ] ], [ "Andorra", "ad", "376" ], [ "Angola", "ao", "244" ], [ "Anguilla", "ai", "1", 6, [ "264" ] ], [ "Antigua and Barbuda", "ag", "1", 7, [ "268" ] ], [ "Argentina", "ar", "54" ], [ "Armenia (Հայաստան)", "am", "374" ], [ "Aruba", "aw", "297" ], [ "Ascension Island", "ac", "247" ], [ "Australia", "au", "61", 0 ], [ "Austria (Österreich)", "at", "43" ], [ "Azerbaijan (Azərbaycan)", "az", "994" ], [ "Bahamas", "bs", "1", 8, [ "242" ] ], [ "Bahrain (‫البحرين‬‎)", "bh", "973" ], [ "Bangladesh (বাংলাদেশ)", "bd", "880" ], [ "Barbados", "bb", "1", 9, [ "246" ] ], [ "Belarus (Беларусь)", "by", "375" ], [ "Belgium (België)", "be", "32" ], [ "Belize", "bz", "501" ], [ "Benin (Bénin)", "bj", "229" ], [ "Bermuda", "bm", "1", 10, [ "441" ] ], [ "Bhutan (འབྲུག)", "bt", "975" ], [ "Bolivia", "bo", "591" ], [ "Bosnia and Herzegovina (Босна и Херцеговина)", "ba", "387" ], [ "Botswana", "bw", "267" ], [ "Brazil (Brasil)", "br", "55" ], [ "British Indian Ocean Territory", "io", "246" ], [ "British Virgin Islands", "vg", "1", 11, [ "284" ] ], [ "Brunei", "bn", "673" ], [ "Bulgaria (България)", "bg", "359" ], [ "Burkina Faso", "bf", "226" ], [ "Burundi (Uburundi)", "bi", "257" ], [ "Cambodia (កម្ពុជា)", "kh", "855" ], [ "Cameroon (Cameroun)", "cm", "237" ], [ "Canada", "ca", "1", 1, [ "204", "226", "236", "249", "250", "289", "306", "343", "365", "387", "403", "416", "418", "431", "437", "438", "450", "506", "514", "519", "548", "579", "581", "587", "604", "613", "639", "647", "672", "705", "709", "742", "778", "780", "782", "807", "819", "825", "867", "873", "902", "905" ] ], [ "Cape Verde (Kabu Verdi)", "cv", "238" ], [ "Caribbean Netherlands", "bq", "599", 1, [ "3", "4", "7" ] ], [ "Cayman Islands", "ky", "1", 12, [ "345" ] ], [ "Central African Republic (République centrafricaine)", "cf", "236" ], [ "Chad (Tchad)", "td", "235" ], [ "Chile", "cl", "56" ], [ "China (中国)", "cn", "86" ], [ "Christmas Island", "cx", "61", 2, [ "89164" ] ], [ "Cocos (Keeling) Islands", "cc", "61", 1, [ "89162" ] ], [ "Colombia", "co", "57" ], [ "Comoros (‫جزر القمر‬‎)", "km", "269" ], [ "Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)", "cd", "243" ], [ "Congo (Republic) (Congo-Brazzaville)", "cg", "242" ], [ "Cook Islands", "ck", "682" ], [ "Costa Rica", "cr", "506" ], [ "Côte d’Ivoire", "ci", "225" ], [ "Croatia (Hrvatska)", "hr", "385" ], [ "Cuba", "cu", "53" ], [ "Curaçao", "cw", "599", 0 ], [ "Cyprus (Κύπρος)", "cy", "357" ], [ "Czech Republic (Česká republika)", "cz", "420" ], [ "Denmark (Danmark)", "dk", "45" ], [ "Djibouti", "dj", "253" ], [ "Dominica", "dm", "1", 13, [ "767" ] ], [ "Dominican Republic (República Dominicana)", "do", "1", 2, [ "809", "829", "849" ] ], [ "Ecuador", "ec", "593" ], [ "Egypt (‫مصر‬‎)", "eg", "20" ], [ "El Salvador", "sv", "503" ], [ "Equatorial Guinea (Guinea Ecuatorial)", "gq", "240" ], [ "Eritrea", "er", "291" ], [ "Estonia (Eesti)", "ee", "372" ], [ "Eswatini", "sz", "268" ], [ "Ethiopia", "et", "251" ], [ "Falkland Islands (Islas Malvinas)", "fk", "500" ], [ "Faroe Islands (Føroyar)", "fo", "298" ], [ "Fiji", "fj", "679" ], [ "Finland (Suomi)", "fi", "358", 0 ], [ "France", "fr", "33" ], [ "French Guiana (Guyane française)", "gf", "594" ], [ "French Polynesia (Polynésie française)", "pf", "689" ], [ "Gabon", "ga", "241" ], [ "Gambia", "gm", "220" ], [ "Georgia (საქართველო)", "ge", "995" ], [ "Germany (Deutschland)", "de", "49" ], [ "Ghana (Gaana)", "gh", "233" ], [ "Gibraltar", "gi", "350" ], [ "Greece (Ελλάδα)", "gr", "30" ], [ "Greenland (Kalaallit Nunaat)", "gl", "299" ], [ "Grenada", "gd", "1", 14, [ "473" ] ], [ "Guadeloupe", "gp", "590", 0 ], [ "Guam", "gu", "1", 15, [ "671" ] ], [ "Guatemala", "gt", "502" ], [ "Guernsey", "gg", "44", 1, [ "1481", "7781", "7839", "7911" ] ], [ "Guinea (Guinée)", "gn", "224" ], [ "Guinea-Bissau (Guiné Bissau)", "gw", "245" ], [ "Guyana", "gy", "592" ], [ "Haiti", "ht", "509" ], [ "Honduras", "hn", "504" ], [ "Hong Kong (香港)", "hk", "852" ], [ "Hungary (Magyarország)", "hu", "36" ], [ "Iceland (Ísland)", "is", "354" ], [ "India (भारत)", "in", "91" ], [ "Indonesia", "id", "62" ], [ "Iran (‫ایران‬‎)", "ir", "98" ], [ "Iraq (‫العراق‬‎)", "iq", "964" ], [ "Ireland", "ie", "353" ], [ "Isle of Man", "im", "44", 2, [ "1624", "74576", "7524", "7924", "7624" ] ], [ "Israel (‫ישראל‬‎)", "il", "972" ], [ "Italy (Italia)", "it", "39", 0 ], [ "Jamaica", "jm", "1", 4, [ "876", "658" ] ], [ "Japan (日本)", "jp", "81" ], [ "Jersey", "je", "44", 3, [ "1534", "7509", "7700", "7797", "7829", "7937" ] ], [ "Jordan (‫الأردن‬‎)", "jo", "962" ], [ "Kazakhstan (Казахстан)", "kz", "7", 1, [ "33", "7" ] ], [ "Kenya", "ke", "254" ], [ "Kiribati", "ki", "686" ], [ "Kosovo", "xk", "383" ], [ "Kuwait (‫الكويت‬‎)", "kw", "965" ], [ "Kyrgyzstan (Кыргызстан)", "kg", "996" ], [ "Laos (ລາວ)", "la", "856" ], [ "Latvia (Latvija)", "lv", "371" ], [ "Lebanon (‫لبنان‬‎)", "lb", "961" ], [ "Lesotho", "ls", "266" ], [ "Liberia", "lr", "231" ], [ "Libya (‫ليبيا‬‎)", "ly", "218" ], [ "Liechtenstein", "li", "423" ], [ "Lithuania (Lietuva)", "lt", "370" ], [ "Luxembourg", "lu", "352" ], [ "Macau (澳門)", "mo", "853" ], [ "North Macedonia (Македонија)", "mk", "389" ], [ "Madagascar (Madagasikara)", "mg", "261" ], [ "Malawi", "mw", "265" ], [ "Malaysia", "my", "60" ], [ "Maldives", "mv", "960" ], [ "Mali", "ml", "223" ], [ "Malta", "mt", "356" ], [ "Marshall Islands", "mh", "692" ], [ "Martinique", "mq", "596" ], [ "Mauritania (‫موريتانيا‬‎)", "mr", "222" ], [ "Mauritius (Moris)", "mu", "230" ], [ "Mayotte", "yt", "262", 1, [ "269", "639" ] ], [ "Mexico (México)", "mx", "52" ], [ "Micronesia", "fm", "691" ], [ "Moldova (Republica Moldova)", "md", "373" ], [ "Monaco", "mc", "377" ], [ "Mongolia (Монгол)", "mn", "976" ], [ "Montenegro (Crna Gora)", "me", "382" ], [ "Montserrat", "ms", "1", 16, [ "664" ] ], [ "Morocco (‫المغرب‬‎)", "ma", "212", 0 ], [ "Mozambique (Moçambique)", "mz", "258" ], [ "Myanmar (Burma) (မြန်မာ)", "mm", "95" ], [ "Namibia (Namibië)", "na", "264" ], [ "Nauru", "nr", "674" ], [ "Nepal (नेपाल)", "np", "977" ], [ "Netherlands (Nederland)", "nl", "31" ], [ "New Caledonia (Nouvelle-Calédonie)", "nc", "687" ], [ "New Zealand", "nz", "64" ], [ "Nicaragua", "ni", "505" ], [ "Niger (Nijar)", "ne", "227" ], [ "Nigeria", "ng", "234" ], [ "Niue", "nu", "683" ], [ "Norfolk Island", "nf", "672" ], [ "North Korea (조선 민주주의 인민 공화국)", "kp", "850" ], [ "Northern Mariana Islands", "mp", "1", 17, [ "670" ] ], [ "Norway (Norge)", "no", "47", 0 ], [ "Oman (‫عُمان‬‎)", "om", "968" ], [ "Pakistan (‫پاکستان‬‎)", "pk", "92" ], [ "Palau", "pw", "680" ], [ "Palestine (‫فلسطين‬‎)", "ps", "970" ], [ "Panama (Panamá)", "pa", "507" ], [ "Papua New Guinea", "pg", "675" ], [ "Paraguay", "py", "595" ], [ "Peru (Perú)", "pe", "51" ], [ "Philippines", "ph", "63" ], [ "Poland (Polska)", "pl", "48" ], [ "Portugal", "pt", "351" ], [ "Puerto Rico", "pr", "1", 3, [ "787", "939" ] ], [ "Qatar (‫قطر‬‎)", "qa", "974" ], [ "Réunion (La Réunion)", "re", "262", 0 ], [ "Romania (România)", "ro", "40" ], [ "Russia (Россия)", "ru", "7", 0 ], [ "Rwanda", "rw", "250" ], [ "Saint Barthélemy", "bl", "590", 1 ], [ "Saint Helena", "sh", "290" ], [ "Saint Kitts and Nevis", "kn", "1", 18, [ "869" ] ], [ "Saint Lucia", "lc", "1", 19, [ "758" ] ], [ "Saint Martin (Saint-Martin (partie française))", "mf", "590", 2 ], [ "Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)", "pm", "508" ], [ "Saint Vincent and the Grenadines", "vc", "1", 20, [ "784" ] ], [ "Samoa", "ws", "685" ], [ "San Marino", "sm", "378" ], [ "São Tomé and Príncipe (São Tomé e Príncipe)", "st", "239" ], [ "Saudi Arabia (‫المملكة العربية السعودية‬‎)", "sa", "966" ], [ "Senegal (Sénégal)", "sn", "221" ], [ "Serbia (Србија)", "rs", "381" ], [ "Seychelles", "sc", "248" ], [ "Sierra Leone", "sl", "232" ], [ "Singapore", "sg", "65" ], [ "Sint Maarten", "sx", "1", 21, [ "721" ] ], [ "Slovakia (Slovensko)", "sk", "421" ], [ "Slovenia (Slovenija)", "si", "386" ], [ "Solomon Islands", "sb", "677" ], [ "Somalia (Soomaaliya)", "so", "252" ], [ "South Africa", "za", "27" ], [ "South Korea (대한민국)", "kr", "82" ], [ "South Sudan (‫جنوب السودان‬‎)", "ss", "211" ], [ "Spain (España)", "es", "34" ], [ "Sri Lanka (ශ්‍රී ලංකාව)", "lk", "94" ], [ "Sudan (‫السودان‬‎)", "sd", "249" ], [ "Suriname", "sr", "597" ], [ "Svalbard and Jan Mayen", "sj", "47", 1, [ "79" ] ], [ "Sweden (Sverige)", "se", "46" ], [ "Switzerland (Schweiz)", "ch", "41" ], [ "Syria (‫سوريا‬‎)", "sy", "963" ], [ "Taiwan (台灣)", "tw", "886" ], [ "Tajikistan", "tj", "992" ], [ "Tanzania", "tz", "255" ], [ "Thailand (ไทย)", "th", "66" ], [ "Timor-Leste", "tl", "670" ], [ "Togo", "tg", "228" ], [ "Tokelau", "tk", "690" ], [ "Tonga", "to", "676" ], [ "Trinidad and Tobago", "tt", "1", 22, [ "868" ] ], [ "Tunisia (‫تونس‬‎)", "tn", "216" ], [ "Turkey (Türkiye)", "tr", "90" ], [ "Turkmenistan", "tm", "993" ], [ "Turks and Caicos Islands", "tc", "1", 23, [ "649" ] ], [ "Tuvalu", "tv", "688" ], [ "U.S. Virgin Islands", "vi", "1", 24, [ "340" ] ], [ "Uganda", "ug", "256" ], [ "Ukraine (Україна)", "ua", "380" ], [ "United Arab Emirates (‫الإمارات العربية المتحدة‬‎)", "ae", "971" ], [ "United Kingdom", "gb", "44", 0 ], [ "United States", "us", "1", 0 ], [ "Uruguay", "uy", "598" ], [ "Uzbekistan (Oʻzbekiston)", "uz", "998" ], [ "Vanuatu", "vu", "678" ], [ "Vatican City (Città del Vaticano)", "va", "39", 1, [ "06698" ] ], [ "Venezuela", "ve", "58" ], [ "Vietnam (Việt Nam)", "vn", "84" ], [ "Wallis and Futuna (Wallis-et-Futuna)", "wf", "681" ], [ "Western Sahara (‫الصحراء الغربية‬‎)", "eh", "212", 1, [ "5288", "5289" ] ], [ "Yemen (‫اليمن‬‎)", "ye", "967" ], [ "Zambia", "zm", "260" ], [ "Zimbabwe", "zw", "263" ], [ "Åland Islands", "ax", "358", 1, [ "18" ] ] ]; // https://github.com/jackocnr/intl-tel-input/blob/master/src/js/data.js
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_invalid_fields')){
    function ifcf7_invalid_fields($fields = [], $contact_form = null){
        if(empty($contact_form)){
    		$contact_form = wpcf7_get_current_contact_form();
    	}
        $invalid = [];
        if(empty($contact_form)){
    		return $invalid;
    	}
        $tags = wp_list_pluck($contact_form->scan_form_tags(), 'type', 'name');
        foreach($fields as $name => $type){
    		if(!empty($tags[$name])){
                if(!in_array($tags[$name], (array) $type)){
        			$invalid[] = $name;
        		}
    		}
    	}
        return $invalid;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_meta_pref')){
    function ifcf7_meta_pref($name = '', $contact_form = null, $single = true){
		/*if(null === $contact_form){
            $contact_form = wpcf7_get_current_contact_form();
            if(null === $contact_form){
                return '';
            }
        }*/
        // FIX
        $contact_form = ifcf7_contact_form($contact_form);
        if(null === $contact_form){
            return '';
        }
		return get_post_meta($contact_form->id(), $name, $single);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_missing_fields')){
    function ifcf7_missing_fields($fields = [], $contact_form = null){
        if(empty($contact_form)){
    		$contact_form = wpcf7_get_current_contact_form();
    	}
        $missing = [];
        if(empty($contact_form)){
    		return $missing;
    	}
        $tags = wp_list_pluck($contact_form->scan_form_tags(), 'type', 'name');
		if(ifwp_is_array_assoc($fields)){
			$fields = array_keys($fields);
		}
        foreach($fields as $name){
    		if(empty($tags[$name])){
    			$missing[] = $name;
    		}
    	}
        return $missing;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_parse_referer_tag')){
    function ifcf7_parse_referer_tag($referer_tag = ''){
		if(!$referer_tag and isset($_GET['ifcf7_referer'])){
			$referer_tag = $_GET['ifcf7_referer'];
		}
		$referer_tag = wpcf7_sanitize_unit_tag($referer_tag);
		$pattern = '/^(d|i|u){1}-c([0-9]+)-f([0-9]+)-n([a-f0-9]{10})-(p|u){1}([0-9]+)$/';
		if(!preg_match_all($pattern, $referer_tag, $matches)){
			return [];
		}
		switch($matches[1][0]){
			case 'd':
				$action = 'delete';
				break;
			case 'i':
				$action = 'insert';
				break;
			case 'u':
				$action = 'update';
				break;
		}
		$container_post_id = $matches[2][0];
		$form_id = $matches[3][0];
		$nonce = $matches[4][0];
		if('p' === $matches[5][0]){
			$type = 'post';
		} else {
			$type = 'user';
		}
		$object_id = $matches[6][0];
		return [
			'action' => $action,
			'container_post_id' => (int) $container_post_id,
			'form_id' => (int) $form_id,
			'nonce_valid' => wp_verify_nonce($nonce, 'ifcf7-' . $action . '-' . $type . '_' . $object_id),
			'object_id' => (int) $object_id,
			'type' => $type,
		];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_placeholder_value')){
    function ifcf7_placeholder_value($tag = null, $fallback = ''){
		$placeholder = _ifcf7_placeholder_value($tag, $fallback);
		if(!$placeholder){
			return $placeholder;
		}
		if('file' === $tag->basetype){
			return $placeholder;
		}
		$fa_icon = ifcf7_fa_icon($tag);
		if(!$fa_icon){
			return $placeholder;
		}
		//return $fa_icon . '&nbsp;' . $placeholder;
		return $fa_icon . $placeholder;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_pref')){
    function ifcf7_pref($name = '', $contact_form = null){
		return ifcf7_additional_setting($name, true, $contact_form);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_render_admin_table')){
	function ifcf7_render_admin_table(...$args){
        return IFCF7\Admin_Table::render_admin_table(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_seems_false')){
    function ifcf7_seems_false($name = '', $contact_form = null){
		if(null === $contact_form){
			$contact_form = wpcf7_get_current_contact_form();
			if(null === $contact_form){
				return true;
			}
		}
		return ifwp_seems_false((string) $contact_form->pref($name));
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifcf7_seems_true')){
    function ifcf7_seems_true($name = '', $contact_form = null){
		if(null === $contact_form){
			$contact_form = wpcf7_get_current_contact_form();
			if(null === $contact_form){
				return false;
			}
		}
		return ifwp_seems_true((string) $contact_form->pref($name));
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
