# Simple Voting System

## System Overview
The Simple Voting System is a comprehensive platform designed to facilitate transparent and efficient voting processes. It provides an easy-to-use interface for both administrators and voters, ensuring secure and reliable vote management.

## User Roles and Permissions
The system supports different user roles with specific permissions:
- **Administrators:** Can create and manage questions, options, and view voting results.
- **Authenticated Users:** Can view and participate in active voting questions.
- **Anonymous Users:** Can view active voting questions (if enabled in settings).

## Administrator Guide
As an administrator, you can manage the voting system through these main sections:
- **Questions:** Create, edit, and manage voting questions.
- **Options:** Add and manage voting options for each question.
- **Settings:** Configure system-wide settings and permissions.
- **Results:** View real-time voting results and statistics.

## Voter Guide
1. Navigate to the voting page to see available questions.
2. Select a voting question to participate in.
3. Review the question and available options carefully.
4. Choose your preferred option by selecting the radio button.
5. Submit your vote using the "Vote" button.
6. Wait for the confirmation message.

## About the Simple Voting System
**simple_voting** is a modular system for polls and voting in Drupal. It consists of two complementary modules:
- **simple_voting:** Provides the user interface, administration of questions and options, and the main voting logic.
- **simple_voting_rest:** Exposes a RESTful API to interact with the voting system from external applications. It depends on the simple_voting module.

---

# simple_voting_rest

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

## System Features
- **User-Friendly Interface:** Clean and intuitive design for easy navigation.
- **Multiple Question Support:** Create and manage multiple voting questions simultaneously.
- **Real-Time Results:** Instant vote counting and result visualization.
- **Access Control:** Role-based permissions for enhanced security.
- **Mobile Responsive:** Fully functional on all device sizes.
- **Result Statistics:** Detailed vote statistics for administrators.
- **Customizable Options:** Flexible option configuration for each question.

## Technical Requirements
- **Browser Support:** Modern web browsers (Chrome, Firefox, Safari, Edge)
- **Internet Connection:** Required for real-time voting and result updates
- **Screen Resolution:** Minimum 320px width (mobile-responsive)


## License
MIT
