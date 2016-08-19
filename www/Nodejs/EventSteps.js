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
	var PRAY;
	var CONSUME;
	var DISCUSS;
	var PRE_CHUNKING;
	var CHUNKING;
	var BLIND_DRAFT;
	var SELF_CHECK;
	var SELF_CHECK_FULL;
	var PEER_REVIEW;
	var KEYWORD_CHECK;
	var CONTENT_REVIEW;
	var FINISHED;

	// Define which variables and methods can be accessed
	return {
		PRAY: "pray",
		CONSUME: "consume",
		DISCUSS: "discuss",
		PRE_CHUNKING: "pre-chunking",
		CHUNKING: "chunking",
		BLIND_DRAFT: "blind-draft",
		SELF_CHECK: "self-check",
		SELF_CHECK_FULL: "self-check-full",
		PEER_REVIEW: "peer-review",
		KEYWORD_CHECK: "keyword-check",
		CONTENT_REVIEW: "content-review",
		FINISHED: "finished",
	}
};

// Export the EventSteps class so you can use it in
// other files by using require("EventSteps").EventSteps
exports.EventSteps = EventSteps;