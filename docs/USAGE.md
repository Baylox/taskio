# Usage Guide

This guide explains how to use Taskio's features for project and task management.

## Table of Contents

- [Getting Started](#getting-started)
- [Board Management](#board-management)
- [Lane Management](#lane-management)
- [Card Management](#card-management)
- [Collaboration](#collaboration)
- [User Profile](#user-profile)
- [Admin Features](#admin-features)

## Getting Started

### First Steps

1. **Create an Account**
   - Navigate to the registration page
   - Enter your name, email, and password
   - Verify your email address (check Mailpit in development)

2. **Log In**
   - Use your credentials to log in
   - Check "Remember Me" to stay logged in

3. **Navigate the Dashboard**
   - Access "My Boards" to see your boards
   - View boards you own and boards shared with you

## Board Management

### Creating a Board

1. Click **"My Boards"** in the navigation menu
2. Click the **"Create Board"** button
3. Enter a descriptive title for your board
4. Click **"Save"** to create the board

### Viewing Boards

- **My Boards**: View all boards you've created
- **Shared with Me**: View boards where you're a collaborator
- Click on any board to open it

### Editing a Board

1. Open the board you want to edit
2. Click the **"Edit Board"** button
3. Update the board title
4. Manage collaborators (see [Collaboration](#collaboration))
5. Save your changes

### Deleting a Board

1. Open the board you want to delete
2. Click **"Edit Board"**
3. Click the **"Delete Board"** button
4. Confirm the deletion

**Note**: Only board owners can delete boards. Deleting a board will permanently remove all lanes, cards, and data.

## Lane Management

Lanes are vertical columns that organize your workflow (e.g., "To Do", "In Progress", "Done").

### Creating a Lane

1. Open a board
2. Click **"Add Lane"** or similar button
3. Enter a lane title (e.g., "Backlog", "In Progress")
4. Save to create the lane

### Reordering Lanes

- Drag and drop lanes horizontally to reorder them
- The position is automatically saved

### Editing a Lane

1. Click on the lane title or edit button
2. Update the lane name
3. Save your changes

### Deleting a Lane

1. Click the delete button on the lane
2. Confirm the deletion

**Warning**: Deleting a lane will also delete all cards within that lane.

## Card Management

Cards represent individual tasks or items within a lane.

### Creating a Card

1. Navigate to the lane where you want to add a card
2. Click **"Add Card"** at the bottom of the lane
3. Enter the card details:
   - **Title**: Brief description of the task
   - **Description**: Detailed information (optional)
   - **Status**: Select from Todo, In Progress, or Done
4. Click **"Save"** to create the card

### Viewing Card Details

- Click on any card to open its detail view
- View the full title, description, and status

### Editing a Card

1. Click on the card to open it
2. Click **"Edit"** or modify the fields directly
3. Update:
   - Title
   - Description
   - Status (Todo, In Progress, Done)
4. Save your changes

### Moving Cards

**Between Lanes:**
- Drag and drop a card from one lane to another
- The card will be moved to the new lane

**Within a Lane:**
- Drag and drop cards to reorder them vertically
- The position is automatically saved

### Deleting a Card

1. Open the card detail view
2. Click the **"Delete"** button
3. Confirm the deletion

## Collaboration

Taskio allows you to share boards with team members and collaborate in real-time.

### Inviting Collaborators

1. Open the board you own
2. Click **"Edit Board"** or navigate to board settings
3. In the **"Collaborators"** section:
   - Enter the email address of the person you want to invite
   - Click **"Send Invitation"**
4. The recipient will receive an email with an invitation link

### Accepting Invitations

1. Check your email for the board invitation
2. Click the invitation link
3. Log in to your Taskio account (or create one if needed)
4. The board will now appear in your **"Shared with Me"** section

### Collaborator Permissions

**Board Owner:**
- Full control over the board
- Can add/edit/delete lanes and cards
- Can invite and remove collaborators
- Can delete the board

**Collaborator:**
- Can view the board
- Can add/edit/delete cards
- Can reorder lanes and cards
- Cannot delete the board or manage other collaborators

### Removing Collaborators

1. Open the board settings
2. Find the collaborator in the list
3. Click **"Remove"** next to their name
4. The user will no longer have access to the board

## User Profile

### Updating Your Profile

1. Click on your name or profile icon in the navigation
2. Navigate to **"Profile"** or **"Settings"**
3. Update your information:
   - Name
   - Email address
   - Password
4. Save your changes

### Changing Your Password

1. Go to your profile settings
2. Enter your current password
3. Enter your new password
4. Confirm the new password
5. Save the changes

### Password Reset

If you forgot your password:

1. Click **"Forgot Password?"** on the login page
2. Enter your email address
3. Check your email for a password reset link
4. Click the link and set a new password

## Admin Features

Administrators have special privileges for managing the entire system.

### Accessing the Admin Dashboard

1. Log in with an admin account
2. Click **"Admin"** or **"Dashboard"** in the navigation
3. The admin panel will display system-wide information

### Managing Boards

**View All Boards:**
- See all boards in the system, regardless of ownership

**Search Boards:**
- Search by board title or owner name
- Filter boards to find specific ones

**Delete Any Board:**
- Administrators can delete any board
- Use this feature carefully as it's irreversible

### Managing Users

**View All Users:**
- See all registered users in the system

**User Information:**
- View user details (name, email, registration date)
- See the number of boards owned by each user

**Note**: For security reasons, admins cannot view or change user passwords.

## Tips and Best Practices

### Organizing Your Workflow

1. **Create Clear Lane Names**: Use descriptive names like "Backlog", "To Do", "In Progress", "Review", "Done"
2. **Keep Cards Focused**: Each card should represent a single task or item
3. **Use Descriptions**: Add detailed information in card descriptions
4. **Update Status Regularly**: Keep card statuses current for accurate tracking

### Collaboration Tips

1. **Invite Relevant People**: Only invite team members who need access
2. **Use Clear Titles**: Make board and card titles descriptive for all collaborators
3. **Regular Updates**: Keep cards and statuses updated for team visibility
4. **Communication**: Use card descriptions to communicate context and requirements

### Performance Tips

1. **Archive Old Boards**: Delete or archive completed project boards
2. **Limit Cards per Lane**: Keep lanes manageable (aim for 10-20 active cards)
3. **Regular Cleanup**: Remove completed or obsolete cards

## Troubleshooting

### Common Issues

**Can't see a board:**
- Verify you have permission to access it
- Check if you're in the correct section (My Boards vs. Shared with Me)

**Drag and drop not working:**
- Ensure JavaScript is enabled in your browser
- Try refreshing the page
- Clear browser cache

**Email not received:**
- Check your spam/junk folder
- In development, check Mailpit (http://localhost:8025)
- Verify your email address is correct

**Changes not saving:**
- Check your internet connection
- Ensure you have the necessary permissions
- Try refreshing the page and making the change again

## Getting Help

If you need additional assistance:

1. Check the [Installation Guide](INSTALLATION.md) for setup issues
2. Review the [Architecture Documentation](ARCHITECTURE.md) for technical details

---

[← Back to README](../README.md) | [Installation Guide](INSTALLATION.md) | [Testing Guide →](TESTING.md)
