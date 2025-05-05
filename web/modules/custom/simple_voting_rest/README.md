# Simple Voting REST API

This module exposes a RESTful API for the Simple Voting System in Drupal, allowing external applications and frontends to interact with the voting system.

## REST Endpoints Examples

- `GET /api/simple-voting/questions`: Get all active questions.
- `GET /api/simple-voting/questions/{voting_question}`: Get details of a specific question.
- `POST /api/simple-voting/{voting_question}/vote`: Submit a vote for an option in a question.

### How to Vote via API (POST)
To cast a vote using the REST API, send a **POST** request to `/api/simple-voting/{voting_question}/vote` with the following JSON body:

```json
{
  "option_id": 5
}
```
Replace `5` with the ID of the option you want to vote for.

### Authentication
You can authenticate via session cookie (from a logged-in Drupal user) or HTTP Basic Auth. If you use a cookie, copy it from your browser after logging in.

### Example Successful Response
```json
{
  "status": "success"
}
```
If the question is configured to show results, you may receive:
```json
{
  "votes": 10
}
```
### Vote Once Per User
Each authenticated user can only vote once per question. If you try to vote again, you will receive an error response like:
```json
{
  "error": "You have already voted for this question.",
  "voted_option_id": 5
}
```
### Option Voted by User
When requesting a question via GET, the response includes a `voted_option_id` field indicating the option you have voted for (if any):
```json
{
  "id": 1,
  "title": "Example question",
  ...
  "voted_option_id": 5
}
```
### Error Responses
```json
{
  "message": "Invalid option_id."
}
```

---

## Features
- Prevents double voting for authenticated users.
- Only shows vote counts if results are enabled and the user has voted.
- Provides clear error messages and documentation.
- Fully integrated with the Simple Voting UI module.

## Requirements
- Drupal 10/11
- simple_voting module enabled

## License
MIT
