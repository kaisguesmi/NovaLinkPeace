# Social Media Posts System - Implementation Guide

## Overview
This system transforms the PeaceLink platform into a social media-style posts system with reactions and comments, similar to Facebook/Instagram.

## Database Setup

### Step 1: Run the main schema
```bash
mysql -u root -p < database/schema.sql
```

### Step 2: Run the posts schema
```bash
mysql -u root -p < database/schema_posts.sql
```

### Step 3: Add avatar column to Client table (if needed)
```sql
ALTER TABLE Client ADD COLUMN avatar VARCHAR(255) NULL;
```

## New Tables Created

1. **Post** - Stores user posts with title, content, and optional image
2. **Reaction** - Stores user reactions (like, love, laugh, angry) to posts
3. **PostComment** - Stores comments on posts

## Features Implemented

### 1. Dashboard (`/dashboard`)
- Main page after login
- Shows "Create Post" form at the top
- Displays feed of all posts
- Each post shows:
  - Author avatar and name
  - Post content (title + text + optional image)
  - Time ago
  - Reaction buttons (👍❤️😂😡)
  - Comment button
  - Comments section (expandable)

### 2. Post Creation
- Form with title (optional), content (required), and image upload
- Images stored in `public/assets/images/posts/`
- Posts are immediately visible in the feed

### 3. Reactions
- Four reaction types: like (👍), love (❤️), laugh (😂), angry (😡)
- Users can toggle reactions (clicking same reaction removes it)
- Only one reaction per user per post
- Reaction counts displayed on each post

### 4. Comments
- Users can comment on any post
- Comments show author avatar, name, and time
- Comments section is expandable/collapsible
- Comment form appears at bottom of comments section

### 5. Navigation
- **Dashboard** - Main posts feed
- **Stories** - Legacy stories page (can be updated later)
- **Initiatives** - Existing initiatives
- **Users** - User list from database
- **Settings** - User profile page

## File Structure

### Controllers
- `app/controllers/DashboardController.php` - Main dashboard
- `app/controllers/PostController.php` - Post CRUD and reactions
- `app/controllers/CommentController.php` - Comment management
- `app/controllers/UserController.php` - User list
- `app/controllers/AuthController.php` - Updated to redirect to dashboard

### Models
- `app/models/Post.php` - Post operations
- `app/models/Reaction.php` - Reaction operations
- `app/models/PostComment.php` - Comment operations

### Views
- `app/views/dashboard/index.php` - Dashboard main view
- `app/views/partials/post-card.php` - Post card component
- `app/views/user/index.php` - User list
- `app/views/layouts/back.php` - Updated backend layout

### CSS
- `public/assets/css/backoffice.css` - Extended with post card styles

## Routes

- `/dashboard` - Main dashboard
- `/post/create` - Create post form
- `/post/store` - Store new post (POST)
- `/post/react` - Toggle reaction (POST, AJAX)
- `/post/delete` - Delete post
- `/comment/store` - Add comment (POST, AJAX)
- `/user/index` - User list

## JavaScript Functions

All JavaScript is included in `app/views/layouts/back.php`:

- `toggleReaction(postId, type)` - Toggle reaction on a post
- `toggleComments(postId)` - Show/hide comments section
- `submitComment(event, postId)` - Submit new comment
- `deletePost(postId)` - Delete a post (with confirmation)

## Styling

The post cards use the same design system as the admin dashboard:
- Same color palette (bleu-pastel, vert-doux, orange-chaud, etc.)
- Rounded corners (12px border-radius)
- Card shadows
- Smooth transitions
- Responsive design

## Image Upload

- Images are uploaded to `public/assets/images/posts/`
- Filenames are unique (using uniqid)
- Original file extensions are preserved
- Images are displayed with max-height of 500px

## User Experience

1. **After Login**: User is redirected to `/dashboard`
2. **Create Post**: Form at top of dashboard
3. **View Posts**: Scrollable feed below create form
4. **Interact**: Click reaction buttons or comment button
5. **Comments**: Click "Commenter" to expand comments section

## Notes

- The old "Histoire" system is still in place but not used in the dashboard
- Posts are separate from Histoires
- Reactions replace the old reaction system
- Comments use PostComment table, separate from Commentaire table
- Avatar support is ready but requires users to upload avatars (not implemented in UI yet)

## Future Enhancements

- Real-time updates (WebSockets)
- Image preview before upload
- Edit posts
- Reply to comments (nested comments)
- Post sharing
- Notifications for reactions/comments
- User avatars upload interface

