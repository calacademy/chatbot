<?php

	require_once('../classes/Chatbot.php');
	require('../private/credentials.php');

	$vanuatuMapLinks = MapUtils::getMapLinks('-15.443749,166.474307', $credentials);
	$philippinesMapLinks = MapUtils::getMapLinks('11.5563841,113.5790874', $credentials);
	
	$foo = new Chatbot(array(
		'content' => array(
			'So long, {NAME_AVATAR}.'
		)
	), array(
		1 => array(
			'content' => array(
				'Welcome to Divebot! You\'re about to descend nearly 500 feet (~150 meters) to the "twilight zone,” a dark & mysterious layer of ocean rarely visited by humans. Your mission: Stay alive, observe deep reefs, & find new species!',
				'The boat\'s almost ready to leave—we\'d better get started. What’s your name?'
			),
			'response' => array(
				'type' => 'text',
				'maxlength' => 25
			),
			'target_variable' => 'name_avatar',
			'destination' => 2
		),
		2 => array(
			'content' => array(
				'Hi, {NAME_AVATAR}! Glad to have you on the team. Which expedition do you want to join today?',
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'generic',
							'elements' => array(
								array(
									'title' => 'Vanuatu',
									'subtitle' => 'South Pacific Ocean',
									'image_url' => $vanuatuMapLinks['imgUrl'],
									'buttons' => array(
										array(
											'type' => 'web_url',
											'url' => 'https://www.google.com/maps/place/Vanuatu/@-15.4379277,165.3510218,7z/data=!3m1!4b1!4m5!3m4!1s0x6e89605ec8926013:0x348339cfbed0266a!8m2!3d-15.376706!4d166.959158',
											'title' => 'See it on a map'
										),
										array(
											'type' => 'postback',
											'title' => 'Let\'s go!',
											'payload' => json_encode(array(
												'step' => 2,
												'value' => array(
													array(
														'key' => 'location_choice',
														'value' => 'Vanuatu'
													),
													array(
														'key' => 'dive_site',
														'value' => 'Cliffhanger'
													)
												)
											))
										)
									)
								),
								array(
									'title' => 'Philippines',
									'subtitle' => 'Western Pacific Ocean',
									'image_url' => $philippinesMapLinks['imgUrl'],
									'buttons' => array(
										array(
											'type' => 'web_url',
											'url' => 'https://www.google.com/maps/place/Philippines/@11.5563841,113.5790874,5z/data=!3m1!4b1!4m5!3m4!1s0x324053215f87de63:0x784790ef7a29da57!8m2!3d12.879721!4d121.774017',
											'title' => 'See it on a map'
										),
										array(
											'type' => 'postback',
											'title' => 'Let\'s go!',
											'payload' => json_encode(array(
												'step' => 2,
												'value' => array(
													array(
														'key' => 'location_choice',
														'value' => 'Philippines'
													),
													array(
														'key' => 'dive_site',
														'value' => 'Devil\'s Point'
													)
												)
											))
										)
									)
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					array(
						array(
							'key' => 'location_choice',
							'value' => 'Vanuatu'
						),
						array(
							'key' => 'dive_site',
							'value' => 'Cliffhanger'
						)
					),
					array(
						array(
							'key' => 'location_choice',
							'value' => 'Philippines'
						),
						array(
							'key' => 'dive_site',
							'value' => 'Devil\'s Point'
						)
					)
				)
			),
			'target_variable' => array(
				'location_choice',
				'dive_site'
			),
			'destination' => 3
		),
		3 => array(
			'content' => array(
				'Say hello to {LOCATION_CHOICE}! Your team is already busy loading gear onto the boat. For safety, everyone has a dive buddy underwater—yours is over there. What\'s her/his name?'
			),
			'response' => array(
				'type' => 'text',
				'maxlength' => 21
			),
			'target_variable' => 'name_buddy',
			'destination' => 4
		),
		4 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Nice. {NAME_BUDDY} is a legend around here, one of the most experienced divers you\'ll meet. Since this is one of your first dives, it\'s a perfect match—{NAME_BUDDY} will help you along the way.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Got it',
									'payload' => json_encode(array(
										'step' => 4,
										'value' => 1
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					1
				)
			),
			'destination' => 5
		),
		5 => array(
			'content' => array(
				'Uh-oh, the boat captain wants to see your scientific-diving card—we\'d better make one. Start by messaging a passport-style photo of your face. (Not feeling photogenic? Just type "skip.")'
			),
			'response' => array(
				'type' => 'text||selfie'
			),
			'target_variable' => 'selfie',
			'destination' => 6
		),
		6 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Got it. Next step: Pick a wetsuit color.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Orange',
									'payload' => json_encode(array(
										'step' => 6,
										'value' => 'Orange'
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Ocean camo',
									'payload' => json_encode(array(
										'step' => 6,
										'value' => 'Ocean camo'
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Dazzle',
									'payload' => json_encode(array(
										'step' => 6,
										'value' => 'Dazzle'
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					'Orange',
					'Ocean camo',
					'Dazzle'
				)
			),
			'target_variable' => 'suit_color',
			'destination' => 7
		),
		7 => array(
			'content' => array(
				'Looking good, {NAME_AVATAR}, {SUIT_COLOR} is the coolest. Now we just need to—',
				'{NAME_BUDDY} says: "By the way, when you gotta go, you gotta go. Peeing in your wetsuit is normal on long dives, just remember to give it an extra-long rinse later. That smell is even scarier than running low on air."',
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Ew, sorry about that—not all of {NAME_BUDDY}\'s advice is polite. What color fins do you want to grab?',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Black',
									'payload' => json_encode(array(
										'step' => 7,
										'value' => 'Black'
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Hot pink',
									'payload' => json_encode(array(
										'step' => 7,
										'value' => 'Hot pink'
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Blue',
									'payload' => json_encode(array(
										'step' => 7,
										'value' => 'Blue'
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					'Black',
					'Hot pink',
					'Blue'
				)
			),
			'target_variable' => 'fin_color',
			'destination' => 8
		),
		8 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Got it. Like your teammates, you\'ll also be carrying three 18-to-35-pound tanks containing different mixes of oxygen, nitrogen, and helium. That\'ll make it possible for you to breathe at different depths (even as ocean pressure changes), and give you extra air in case anything goes wrong.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Roger that',
									'payload' => json_encode(array(
										'step' => 8,
										'destination' => 10
									))
								),
								array(
									'type' => 'postback',
									'title' => 'What could go wrong?',
									'payload' => json_encode(array(
										'step' => 8,
										'destination' => 9
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					9,
					10
				)
			)
		),
		9 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Lots! This team has to be ready for anything, from equipment failure to getting swept off-course by strong currents. And since coming up too quickly is dangerous (more on that later), divers have to be prepared to solve each and every problem while still underwater.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Whoa, heavy—let\'s go',
									'payload' => json_encode(array(
										'step' => 9,
										'destination' => 10
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					10
				)
			)
		),
		10 => array(
			'content' => array(
				'Now for the other equipment. You and your teammates are already carrying transect tape (for reef surveys), underwater lights, wet notes (for writing underwater), compasses, depth gauges, fish decompression chambers, dive computers, surface marker buoys, extra masks, lifeline radios, & more.',
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'What else you do want to bring?',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => '4K video camera',
									'payload' => json_encode(array(
										'step' => 10,
										'destination' => 11
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Net & collecting bag',
									'payload' => json_encode(array(
										'step' => 10,
										'destination' => 12
									))
								),
								array(
									'type' => 'postback',
									'title' => 'All of it',
									'payload' => json_encode(array(
										'step' => 10,
										'destination' => 13
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					11,
					12,
					13
				)
			)
		),
		11 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Good thinking, but to scientifically identify new species, we\'ll need actual specimens—and your job is to catch them. Let {NAME_BUDDY} take the camera.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Got it—gimme the net',
									'payload' => json_encode(array(
										'step' => 11,
										'destination' => 14
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					14
				)
			)
		),
		12 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Good choice. You have two tasks on today\'s dive: Stay alive, and catch new specimens.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'I was born for this',
									'payload' => json_encode(array(
										'step' => 12,
										'destination' => 14
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					14
				)
			)
		),
		13 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Oops, you just got denied by your Dive Safety Officer (that\'s who\'s in charge of the team). Deep dives are complicated even if you\'re not working, so for safety, each diver is only allowed one extra scientific task. Today, yours is to catch specimens.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Got it—gimme the net',
									'payload' => json_encode(array(
										'step' => 13,
										'destination' => 14
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					14
				)
			)
		),
		14 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Cool, almost done. Now for the snacks. After a deep dive, you\'ll need to spend another 3 to 4 hours coming up very, very slowly. We call it "decompression" ("deco" if you\'re sassy) because it gives your body time to safely get rid of the gasses you absorb while underwater at higher pressures. It’s not a process you want to rush—seriously.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Wait, deco what?',
									'payload' => json_encode(array(
										'step' => 14,
										'destination' => 15
									))
								),
								array(
									'type' => 'postback',
									'title' => 'You mentioned snacks',
									'payload' => json_encode(array(
										'step' => 14,
										'destination' => 16
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					15,
					16
				)
			)
		),
		15 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Imagine yourself as a bottle of soda. Now shake. If you ascend too quickly after a dive, the gasses in your bloodstream behave just like shaken-up soda when someone suddenly twists off the cap. Decompression is like removing the same cap very, very slowly, allowing divers enough time to "off-gas" safely.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Yikes. Ok, snack me!',
									'payload' => json_encode(array(
										'step' => 15,
										'destination' => 16
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					16
				)
			)
		),
		16 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'You know what you get when you spend up to 5 hours underwater? Hungry! Fortunately, skilled divers can actually eat underwater, and because you lose 2000-3000 calories on a long dive, it’s important to keep your energy up. What snacks do you want to take?',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Bananas please',
									'payload' => json_encode(array(
										'step' => 16,
										'destination' => 17
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Gummis sound good',
									'payload' => json_encode(array(
										'step' => 16,
										'destination' => 18
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Ask {NAME_BUDDY}',
									'payload' => json_encode(array(
										'step' => 16,
										'destination' => 19
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					17,
					18,
					19
				)
			)
		),
		17 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Good choice! Bananas are surprisingly easy to eat underwater (though they do get kinda salty). Plus, they\'re high in potassium, which helps prevent muscle cramps.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'I\'m clearly a genius',
									'payload' => json_encode(array(
										'step' => 17,
										'destination' => 20
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					20
				)
			)
		),
		18 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Gummis are so dense and chewy they can actually clog up your regulator (the device that delivers air from your tanks). Trust us—we learned the hard way. Bananas are the business!',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'K, gimme the \'nanas',
									'payload' => json_encode(array(
										'step' => 18,
										'destination' => 20
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					20
				)
			)
		),
		19 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Smart. {NAME_BUDDY} says, "Go for the bananas! They\'re high in potassium, which helps prevent muscle cramps, and soft enough that they won\'t clog up your regulator."',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Thanks buddy!',
									'payload' => json_encode(array(
										'step' => 19,
										'destination' => 20
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					20
				)
			)
		),
		20 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'You\'re geared up! Time to head out. {NAME_BUDDY} will tell you more about today\'s dive on the boat, while Divebot gets your card ready.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Gimme the deets',
									'payload' => json_encode(array(
										'step' => 20,
										'destination' => array(
											'key' => 'location_choice',
											'choices' => array(
												array(
													'value' => 'Vanuatu',
													'destination' => 21
												),
												array(
													'value' => 'Philippines',
													'destination' => 22
												),
											)
										)
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					21,
					22
				)
			)
		),
		21 => array(
			'content' => array(
				'We did a shallower dive here yesterday to scout locations. At about 150 feet, we found the edge of a sheer drop-off that disappears into the deep. The water’s pretty clear here, and we were able to see big, coral outcroppings on the wall below. Since we don’t know what we’ll find, we’re calling the wall “Cliffhanger.”',
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Okay, we\'re right over {DIVE_SITE}—time to get your rebreather ready. This is the equipment that makes it possible for you to descend way, way below the normal limits for divers.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Cool! Tell me more',
									'payload' => json_encode(array(
										'step' => 21,
										'destination' => 23 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'I\'m savvy—let\'s go',
									'payload' => json_encode(array(
										'step' => 21,
										'destination' => 26 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					23,
					26
				)
			)
		),
		22 => array(
			'content' => array(
				'A while ago, we heard a rumor that radar had mapped a huge pinnacle more than 400 feet below. Yesterday, our boat captain found it. No one\'s ever seen the pinnacle in person before—perfect place to look for new species. All we know for sure is that it\'ll be cold, pitch-black, and that it\'s nicknamed “Devil\'s Point.”',
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Okay, we\'re right over {DIVE_SITE}—time to get your rebreather ready. This is the equipment that makes it possible for you to descend way, way below the normal limits for divers.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Cool! Tell me more',
									'payload' => json_encode(array(
										'step' => 22,
										'destination' => 23 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'I\'m savvy—let\'s go',
									'payload' => json_encode(array(
										'step' => 22,
										'destination' => 26 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					23,
					26
				)
			)
		),
		23 => array(
			'content' => array(
				'You probably already know that you exhale carbon dioxide, but CO² isn’t all you exhale—about 18% of it is actually unused oxygen. Regular scuba gear (called “open-circuit”) lets all that CO² and oxygen bubble right out into the water. Rebreathers, on the other hand, don’t let oxygen go to waste.',
				'Rebreathers are "closed-loop" systems that keep your exhaled oxygen in the system, and add extra oxygen as needed. (They also "scrub out" the CO².) That lets you stay underwater way, way longer than usual. Plus, no bubbles to scare the fish! Another thing—',
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => '{NAME_BUDDY} says: "CHIPMUNK ATTACK! Rebreathers also add bursts of \'diluent\' to keep your oxygen from getting too concentrated, and diluent contains helium. When we talk to each other underwater (which works great if you yell), we end up sounding just like … well, wanna try it?"',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Yeah—chipmunk me!',
									'payload' => json_encode(array(
										'step' => 23,
										'destination' => 24 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Nah, I\'m already pro',
									'payload' => json_encode(array(
										'step' => 23,
										'destination' => 26 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					24,
					26
				)
			)
		),
		24 => array(
			'content' => array(
				'Tap the mic symbol, press record, and say "Time to dive, science crusaders!" (Anything less cheesy works too.)'
			),
			'response' => array(
				'type' => 'audio'
			),
			'target_variable' => 'audio',
			'destination' => 25
		),
		25 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Now you\'re speaking our language.',
							'buttons' => array(
								array(
									'type' => 'web_url',
									'url' => 'https://legacy.calacademy.org/chatbot/twilight/helium/?id={ID}&file={AUDIO}',
									'title' => 'Listen 🔈'
								)
							)
						)
					)
				),
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Okay—rebreather ready? Let\'s get that thing on & do a safety check.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'I\'ll do it myself',
									'payload' => json_encode(array(
										'step' => 25,
										'destination' => 27 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Ask for help',
									'payload' => json_encode(array(
										'step' => 25,
										'destination' => 28 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'I\'m scared',
									'payload' => json_encode(array(
										'step' => 25,
										'destination' => 29 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					27,
					28,
					29
				)
			)
		),
		26 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Okay—rebreather ready? Let\'s get that thing on & do a safety check.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'I\'ll do it myself',
									'payload' => json_encode(array(
										'step' => 26,
										'destination' => 27 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Ask for help',
									'payload' => json_encode(array(
										'step' => 26,
										'destination' => 28 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'I\'m scared',
									'payload' => json_encode(array(
										'step' => 26,
										'destination' => 29 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					27,
					28,
					29
				)
			)
		),
		27 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Uh-oh, one of your hoses is twisted—that could have been really bad.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Ask for help',
									'payload' => json_encode(array(
										'step' => 27,
										'destination' => 30 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					30
				)
			)
		),
		28 => array(
			'content' => array(
				'Smart. That\'s what dive buddies are for.'
			),
			'auto-advance' => true,
			'destination' => 31
		),
		29 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'You can do it—{NAME_BUDDY}\'s got your back! Just ask for help.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Check me, buddy!',
									'payload' => json_encode(array(
										'step' => 29,
										'destination' => 31 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					31
				)
			)
		),
		30 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => '{NAME_BUDDY} says: "Fixed it, you\'re set now. Remember to trust your teammates—that\'s the only way we all get back safe."',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Sorry, trust issues',
									'payload' => json_encode(array(
										'step' => 30,
										'destination' => 31 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					31
				)
			)
		),
		31 => array(
			'content' => array(
				'Safety check complete—you\'re good to go!',
				'{NAME_BUDDY} says: "Looking good, {NAME_AVATAR}. Dive site {DIVE_SITE} unlocked! And here comes the card to prove it.',
				array(
					'attachment' => array(
						'type' => 'image',
						'payload' => array(
							'url' => 'https://legacy.calacademy.org/chatbot/twilight/images/mask/?file={SELFIE}'
						)
					)
				),
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Legit! Okay, ready to drop? Better decide fast—you\'re carrying more than 200 pounds of gear and starting to overheat.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'Let\'s do this!',
									'payload' => json_encode(array(
										'step' => 31,
										'destination' => 32 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Maybe?',
									'payload' => json_encode(array(
										'step' => 31,
										'destination' => 33 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'I\'m scared',
									'payload' => json_encode(array(
										'step' => 31,
										'destination' => 34 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					32,
					33,
					34
				)
			)
		),
		32 => array(
			'content' => array(
				'Welcome to {DIVE_SITE}. Let\'s get to work.'
			),
		),
		33 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'This is no time for indecision—go deep or return to shore!',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'I\'m in!',
									'payload' => json_encode(array(
										'step' => 33,
										'destination' => 32 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Too scared!',
									'payload' => json_encode(array(
										'step' => 33,
										'destination' => 35 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					32,
					35
				)
			)
		),
		34 => array(
			'content' => array(
				array(
					'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'button',
							'text' => 'Not feeling mission-ready? That\'s okay—it takes unusual skills and courage to dive this deep. No shame in quitting this game.',
							'buttons' => array(
								array(
									'type' => 'postback',
									'title' => 'No wait! I\'m in',
									'payload' => json_encode(array(
										'step' => 34,
										'destination' => 32 
									))
								),
								array(
									'type' => 'postback',
									'title' => 'Return to shore',
									'payload' => json_encode(array(
										'step' => 34,
										'destination' => 35 
									))
								)
							)
						)
					)
				)
			),
			'response' => array(
				'type' => 'button',
				'choices' => array(
					32,
					35
				)
			)
		),
		35 => array(
			'content' => array(
				'Thanks for trying—have a coconut.'
			),
		),
	));

?>
