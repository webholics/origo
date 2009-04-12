package com.origo
{
	public class RelationshipsHelper
	{
		/**
		 * All possible relationship types
		 */
		public static const types:Array = [
			"knows",
			"acquaintanceof",
			"ambivalentof",
			"ancestorof",
			"antagonistof",
			"apprenticeto",
			"childof",
			"closefriendof",
			"collaborateswith",
			"colleagueof",
			"descendantof",
			"employedby",
			"employerof",
			"enemyof",
			"engagedto",
			"friendof",
			"grandchildof",
			"grandparentof",
			"hasmet",
			"knowsbyreputation",
			"knowsinpassing",
			"knowsof",
			"lifepartnerof",
			"liveswith",
			"lostcontactwith",
			"mentorof",
			"neighborof",
			"parentof",
			"participantin",
			"siblingof",
			"spouseof",
			"workswith",
			"wouldliketoknow"
		];
		
		/**
		 * Humand readable labels
		 */
		public static const labels:Object = {
			knows:				"knows",
			acquaintanceof:		"acquaintance of",
			ambivalentof:		"ambivalent of",
			ancestorof:			"ancestor of", 
			antagonistof:		"antagonist of",
			apprenticeto:		"apprentice to",
			childof:			"child of",
			closefriendof:		"close friend of",
			collaborateswith:	"collaborates with",
			colleagueof:		"colleague of",
			descendantof:		"descendant of",
			employedby:			"employed by",
			employerof:			"employer of",
			enemyof:			"enemy of",
			engagedto:			"engaged to",
			friendof:			"friend of",
			grandchildof:		"grandchild of",
			grandparentof:		"grandparent of",
			hasmet:				"has met",
			knowsbyreputation:	"knows by reputation",
			knowsinpassing:		"knows in passing",
			knowsof:			"knows of",
			lifepartnerof:		"life partner of",
			liveswith:			"lives with",
			lostcontactwith:	"lost contact with",
			mentorof:			"mentor of",
			neighborof:			"neighbor of",
			parentof:			"parent of",
			participantin:		"participant in",
			siblingof:			"sibling of",
			spouseof:			"spouse of",
			workswith:			"works with",
			wouldliketoknow:	"would like to know"
		};
		
		/**
		 * Specific colors per relationship type
		 */
		public static const colors:Object = {
			knows:				"#0B68B9",
			acquaintanceof:		"#0C63B4",
			ambivalentof:		"#0D5DAF",
			ancestorof:			"#0F58AA",
			antagonistof:		"#1052A5",
			apprenticeto:		"#114DA1",
			childof:			"#12479C",
			closefriendof:		"#144297",
			collaborateswith:	"#153C92",
			colleagueof:		"#16378D",
			descendantof:		"#203A91",
			employedby:			"#2A3D94",
			employerof:			"#353F98",
			enemyof:			"#3F429C",
			engagedto:			"#49459F",
			friendof:			"#5348A3",
			grandchildof:		"#5E4AA7",
			grandparentof:		"#684DAA",
			hasmet:				"#7250AE",
			knowsbyreputation:	"#754FA6",
			knowsinpassing:		"#774E9D",
			knowsof:			"#7A4D95",
			lifepartnerof:		"#7D4C8C",
			liveswith:			"#7F4C84",
			lostcontactwith:	"#824B7B",
			mentorof:			"#854A73",
			neighborof:			"#87496A",
			parentof:			"#8A4862",
			participantin:		"#91515D",
			siblingof:			"#995A58",
			spouseof:			"#A06352",
			workswith:			"#A76C4D",
			wouldliketoknow:	"#AF7548"
		};
		
		public function RelationshipsHelper()
		{
			throw new Error("Do not create an instance of RelationshipsHelper. Use static methods and variables.");
		}
	}
}