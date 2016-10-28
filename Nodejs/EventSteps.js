/**************************************************

File name: EventSteps.js

Description: Class EventSteps creation
Created By: Max Murzamuratov
Creation Date: 01-02-2016
Updated Date: 01-02-2016

Classes: EventSteps - An object to hold EventSteps info

Functions: __construct() - create new EventSteps object

***************************************************/

/***************************************************
** Class Member
***************************************************/
var EventSteps = function() {
	var NONE;
	var PRAY;
	var CONSUME;
	var VERBALIZE;
	var CHUNKING;
	var READ_CHUNK;
	var BLIND_DRAFT;
	var SELF_CHECK;
	var PEER_REVIEW;
	var KEYWORD_CHECK;
	var CONTENT_REVIEW;
	var FINAL_REVIEW;
	var FINISHED;

	// Define which variables and methods can be accessed
	return {
		NONE: "none",
		PRAY: "pray",
		CONSUME: "consume",
		VERBALIZE: "verbalize",
		CHUNKING: "chunking",
		READ_CHUNK: "read-chunk",
		BLIND_DRAFT: "blind-draft",
		SELF_CHECK: "self-check",
		PEER_REVIEW: "peer-review",
		KEYWORD_CHECK: "keyword-check",
		CONTENT_REVIEW: "content-review",
		FINAL_REVIEW: "final-review",
		FINISHED: "finished",
	}
};

// Export the EventSteps class so you can use it in
// other files by using require("EventSteps").EventSteps
exports.EventSteps = EventSteps;