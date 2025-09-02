<?php
// includes/views/frontend/ticket-list.php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 20px 0;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .search-bar {
            flex-grow: 1;
            margin: 0 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 15px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
        }

        .user-actions button {
            background-color: white;
            color: #2563eb;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-actions button:hover {
            background-color: #e0e7ff;
        }

        .main-content {
            display: flex;
            gap: 20px;
        }

        .sidebar {
            flex: 0 0 250px;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .sidebar h2 {
            margin-bottom: 20px;
            color: #1d4ed8;
            border-bottom: 2px solid #e0e7ff;
            padding-bottom: 10px;
        }

        .filters div {
            margin-bottom: 15px;
        }

        .filters label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .filters select, .filters input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .filters button {
            width: 100%;
            padding: 10px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .filters button:hover {
            background-color: #1d4ed8;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #1d4ed8;
        }

        .ticket-list {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .ticket-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e0e7ff;
        }

        .ticket-list-header h2 {
            color: #1d4ed8;
        }

        .new-ticket-btn {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease;
        }

        .new-ticket-btn:hover {
            background-color: #1d4ed8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e0e7ff;
        }

        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #64748b;
        }

        tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        tr:hover {
            background-color: #f1f5f9;
        }

        .status {
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-open {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        .status-closed {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .priority-high {
            color: #dc2626;
            font-weight: bold;
        }

        .priority-medium {
            color: #d97706;
            font-weight: bold;
        }

        .priority-low {
            color: #16a34a;
            font-weight: bold;
        }

        .action-btn {
            background: none;
            border: none;
            color: #2563eb;
            cursor: pointer;
            font-size: 16px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            padding: 20px;
            gap: 10px;
        }

        .pagination button {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background-color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button.active {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .pagination button:hover:not(.active) {
            background-color: #f1f5f9;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            max-width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e0e7ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            color: #1d4ed8;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #64748b;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e0e7ff;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .modal-footer button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-secondary {
            background-color: #e0e7ff;
            color: #2563eb;
        }

        .ticket-details {
            margin-bottom: 20px;
        }

        .ticket-details .detail-row {
            display: flex;
            margin-bottom: 15px;
        }

        .ticket-details .detail-label {
            flex: 0 0 120px;
            font-weight: 500;
            color: #64748b;
        }

        .ticket-details .detail-value {
            flex: 1;
        }

        .comments-section {
            margin-top: 30px;
        }

        .comment {
            border: 1px solid #e0e7ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8fafc;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
            color: #64748b;
        }

        .comment-text {
            line-height: 1.5;
        }

        .add-comment {
            margin-top: 20px;
        }

        /* TinyMCE Editor Styles */
        .tox-tinymce {
            border-radius: 5px !important;
            border: 1px solid #ddd !important;
        }

        .editor-container {
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .editor-header {
            background-color: #f8fafc;
            padding: 10px;
            border-bottom: 1px solid #e0e7ff;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .editor-header button {
            background: none;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .editor-header button:hover {
            background-color: #e0e7ff;
        }

        .editor-content {
            min-height: 200px;
            padding: 15px;
            outline: none;
        }

        .editor-content:empty:before {
            content: attr(data-placeholder);
            color: #64748b;
        }

        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            
            .sidebar {
                flex: 0 0 auto;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-bar {
                margin: 0;
                width: 100%;
            }
            
            .modal-content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <header style="max-width: 64.5%;">
        <div class="container">
            <div class="header-content">
                <div class="logo">SupportHub</div>
                <div class="search-bar">
                    <input type="text" placeholder="Search tickets...">
                </div>
                <div class="user-actions">
                    <button id="header-new-ticket">New Ticket</button>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <h2>Filters</h2>
                <div class="filters">
                    <div>
                        <label for="status">Status</label>
                        <select id="status">
                            <option value="all">All Statuses</option>
                            <option value="open">Open</option>
                            <option value="pending">Pending</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div>
                        <label for="priority">Priority</label>
                        <select id="priority">
                            <option value="all">All Priorities</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div>
                        <label for="date">Date</label>
                        <input type="date" id="date">
                    </div>
                    <div>
                        <label for="search">Keyword</label>
                        <input type="text" id="search" placeholder="Enter keyword...">
                    </div>
                    <button>Apply Filters</button>
                </div>
            </div>

            <div class="content">
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3>Open Tickets</h3>
                        <div class="number">24</div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Tickets</h3>
                        <div class="number">12</div>
                    </div>
                    <div class="stat-card">
                        <h3>Closed Today</h3>
                        <div class="number">8</div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Tickets</h3>
                        <div class="number">142</div>
                    </div>
                </div>

                <div class="ticket-list">
                    <div class="ticket-list-header">
                        <h2>Recent Tickets</h2>
                        <button class="new-ticket-btn" id="new-ticket-btn">
                            <i class="fas fa-plus"></i> New Ticket
                        </button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Requester</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tickets-table-body">
                            <!-- Tickets will be dynamically added here -->
                        </tbody>
                    </table>
                    <div class="pagination">
                        <button class="active">1</button>
                        <button>2</button>
                        <button>3</button>
                        <button>4</button>
                        <button>5</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Ticket Modal -->
    <div class="modal" id="new-ticket-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Ticket</h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="new-ticket-form">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="requester">Requester Email</label>
                        <input type="email" id="requester" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" required>
                            <option value="">Select Department</option>
                            <option value="billing">Billing</option>
                            <option value="technical">Technical Support</option>
                            <option value="sales">Sales</option>
                            <option value="general">General Inquiry</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" required>
                            <option value="">Select Priority</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <!-- TinyMCE-like editor -->
                        <div class="editor-container">
                            <div class="editor-header">
                                <button type="button" data-command="bold"><strong>B</strong></button>
                                <button type="button" data-command="italic"><em>I</em></button>
                                <button type="button" data-command="underline"><u>U</u></button>
                                <button type="button" data-command="insertUnorderedList">• List</button>
                                <button type="button" data-command="insertOrderedList">1. List</button>
                                <button type="button" data-command="createLink">🔗 Link</button>
                            </div>
                            <div class="editor-content" id="description" contenteditable="true" data-placeholder="Describe your issue in detail..."></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" id="cancel-new-ticket">Cancel</button>
                <button class="btn-primary" id="submit-new-ticket">Create Ticket</button>
            </div>
        </div>
    </div>

    <!-- View/Edit Ticket Modal -->
    <div class="modal" id="ticket-detail-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="ticket-modal-title">Ticket Details</h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="ticket-details">
                    <div class="detail-row">
                        <div class="detail-label">Ticket ID:</div>
                        <div class="detail-value" id="detail-id"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Subject:</div>
                        <div class="detail-value" id="detail-subject"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Requester:</div>
                        <div class="detail-value" id="detail-requester"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Department:</div>
                        <div class="detail-value" id="detail-department"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Priority:</div>
                        <div class="detail-value" id="detail-priority"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value" id="detail-status"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Created:</div>
                        <div class="detail-value" id="detail-created"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Description:</div>
                        <div class="detail-value" id="detail-description"></div>
                    </div>
                </div>

                <div class="comments-section">
                    <h3>Comments</h3>
                    <div id="comments-container">
                        <!-- Comments will be dynamically added here -->
                    </div>
                    <div class="add-comment">
                        <div class="form-group">
                            <label for="new-comment">Add Comment</label>
                            <!-- TinyMCE-like editor for comments -->
                            <div class="editor-container">
                                <div class="editor-header">
                                    <button type="button" data-command="bold"><strong>B</strong></button>
                                    <button type="button" data-command="italic"><em>I</em></button>
                                    <button type="button" data-command="underline"><u>U</u></button>
                                    <button type="button" data-command="insertUnorderedList">• List</button>
                                    <button type="button" data-command="insertOrderedList">1. List</button>
                                    <button type="button" data-command="createLink">🔗 Link</button>
                                </div>
                                <div class="editor-content" id="new-comment" contenteditable="true" data-placeholder="Type your comment here..."></div>
                            </div>
                        </div>
                        <button class="btn-primary" id="add-comment-btn">Add Comment</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" id="close-ticket-details">Close</button>
                <button class="btn-primary" id="edit-ticket-btn">Edit Ticket</button>
                <button class="btn-primary" id="approve-ticket-btn" style="display: none;">Approve Ticket</button>
            </div>
        </div>
    </div>

    <script>
        // Sample ticket data
        let tickets = [
            {
                id: 1234,
                subject: "Login issues",
                requester: "john.doe@example.com",
                status: "pending",
                priority: "high",
                created: "10 min ago",
                department: "technical",
                description: "I cannot log in to my account. I keep getting an error message saying my password is incorrect, but I'm sure I'm using the correct one."
            },
            {
                id: 1233,
                subject: "Payment not processing",
                requester: "sara.smith@example.com",
                status: "pending",
                priority: "high",
                created: "25 min ago",
                department: "billing",
                description: "I tried to make a payment but it keeps failing. My card has sufficient funds and is not expired."
            },
            {
                id: 1232,
                subject: "Feature request",
                requester: "mark.johnson@example.com",
                status: "open",
                priority: "low",
                created: "1 hour ago",
                department: "sales",
                description: "I would like to request a dark mode feature for the mobile app. It would be easier on the eyes during nighttime use."
            },
            {
                id: 1231,
                subject: "Website loading slow",
                requester: "lisa.wang@example.com",
                status: "closed",
                priority: "medium",
                created: "2 hours ago",
                department: "technical",
                description: "The website has been loading very slowly for the past day. Pages take over 10 seconds to load completely."
            },
            {
                id: 1230,
                subject: "Password reset",
                requester: "robert.brown@example.com",
                status: "open",
                priority: "medium",
                created: "3 hours ago",
                department: "technical",
                description: "I need to reset my password but the reset email is not arriving in my inbox."
            }
        ];

        // DOM Elements
        const newTicketBtn = document.getElementById('new-ticket-btn');
        const headerNewTicketBtn = document.getElementById('header-new-ticket');
        const newTicketModal = document.getElementById('new-ticket-modal');
        const ticketDetailModal = document.getElementById('ticket-detail-modal');
        const closeButtons = document.querySelectorAll('.close-btn');
        const cancelNewTicketBtn = document.getElementById('cancel-new-ticket');
        const submitNewTicketBtn = document.getElementById('submit-new-ticket');
        const closeTicketDetailsBtn = document.getElementById('close-ticket-details');
        const editTicketBtn = document.getElementById('edit-ticket-btn');
        const approveTicketBtn = document.getElementById('approve-ticket-btn');
        const ticketsTableBody = document.getElementById('tickets-table-body');
        const newTicketForm = document.getElementById('new-ticket-form');
        const editorButtons = document.querySelectorAll('.editor-header button');

        // Initialize the app
        function init() {
            renderTickets();
            setupEventListeners();
            setupEditor();
        }

        // Render tickets to the table
        function renderTickets() {
            ticketsTableBody.innerHTML = '';
            
            tickets.forEach(ticket => {
                const row = document.createElement('tr');
                row.dataset.id = ticket.id;
                
                row.innerHTML = `
                    <td>#${ticket.id}</td>
                    <td>${ticket.subject}</td>
                    <td>${ticket.requester}</td>
                    <td><span class="status status-${ticket.status}">${ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1)}</span></td>
                    <td class="priority-${ticket.priority}">${ticket.priority.charAt(0).toUpperCase() + ticket.priority.slice(1)}</td>
                    <td>${ticket.created}</td>
                    <td><button class="action-btn"><i class="fas fa-eye"></i></button></td>
                `;
                
                ticketsTableBody.appendChild(row);
            });
        }

        // Setup event listeners
        function setupEventListeners() {
            // Open new ticket modal
            newTicketBtn.addEventListener('click', () => openModal(newTicketModal));
            headerNewTicketBtn.addEventListener('click', () => openModal(newTicketModal));
            
            // Close modals
            closeButtons.forEach(button => {
                button.addEventListener('click', closeModals);
            });
            
            cancelNewTicketBtn.addEventListener('click', closeModals);
            closeTicketDetailsBtn.addEventListener('click', closeModals);
            
            // Submit new ticket
            submitNewTicketBtn.addEventListener('click', createNewTicket);
            
            // Edit ticket
            editTicketBtn.addEventListener('click', enableEditMode);
            
            // Approve ticket
            approveTicketBtn.addEventListener('click', approveTicket);
            
            // Add comment
            document.getElementById('add-comment-btn').addEventListener('click', addComment);
            
            // Click on ticket row
            ticketsTableBody.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                if (row) {
                    const ticketId = parseInt(row.dataset.id);
                    const ticket = tickets.find(t => t.id === ticketId);
                    if (ticket) {
                        showTicketDetails(ticket);
                    }
                }
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target === newTicketModal) closeModals();
                if (e.target === ticketDetailModal) closeModals();
            });
        }

        // Setup editor functionality
        function setupEditor() {
            editorButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const command = button.dataset.command;
                    const value = button.dataset.value || null;
                    document.execCommand(command, false, value);
                    
                    // Focus back on the editor
                    document.querySelector('.editor-content:focus')?.focus();
                });
            });
        }

        // Open a modal
        function openModal(modal) {
            modal.style.display = 'flex';
        }

        // Close all modals
        function closeModals() {
            newTicketModal.style.display = 'none';
            ticketDetailModal.style.display = 'none';
            document.getElementById('subject').value = '';
            document.getElementById('requester').value = '';
            document.getElementById('department').value = '';
            document.getElementById('priority').value = '';
            document.getElementById('description').innerHTML = '';
        }

        // Create a new ticket
        function createNewTicket() {
            const subject = document.getElementById('subject').value;
            const requester = document.getElementById('requester').value;
            const department = document.getElementById('department').value;
            const priority = document.getElementById('priority').value;
            const description = document.getElementById('description').innerHTML;
            
            if (!subject || !requester || !department || !priority || !description) {
                alert('Please fill in all fields');
                return;
            }
            
            // Generate a new ticket ID
            const newId = Math.max(...tickets.map(t => t.id)) + 1;
            
            // Create new ticket object
            const newTicket = {
                id: newId,
                subject,
                requester,
                department,
                priority,
                description,
                status: 'pending', // New tickets are pending by default
                created: 'Just now',
                comments: []
            };
            
            // Add to tickets array
            tickets.unshift(newTicket);
            
            // Re-render tickets
            renderTickets();
            
            // Close modal and reset form
            closeModals();
            
            // Show success message
            alert('Ticket created successfully! It is now pending admin approval.');
        }

        // Show ticket details
        function showTicketDetails(ticket) {
            document.getElementById('ticket-modal-title').textContent = `Ticket #${ticket.id}`;
            document.getElementById('detail-id').textContent = `#${ticket.id}`;
            document.getElementById('detail-subject').textContent = ticket.subject;
            document.getElementById('detail-requester').textContent = ticket.requester;
            document.getElementById('detail-department').textContent = ticket.department.charAt(0).toUpperCase() + ticket.department.slice(1);
            document.getElementById('detail-priority').textContent = ticket.priority.charAt(0).toUpperCase() + ticket.priority.slice(1);
            document.getElementById('detail-status').textContent = ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1);
            document.getElementById('detail-created').textContent = ticket.created;
            document.getElementById('detail-description').innerHTML = ticket.description;
            
            // Show/hide approve button based on status
            if (ticket.status === 'pending') {
                approveTicketBtn.style.display = 'block';
            } else {
                approveTicketBtn.style.display = 'none';
            }
            
            // Render comments
            const commentsContainer = document.getElementById('comments-container');
            commentsContainer.innerHTML = '';
            
            if (ticket.comments && ticket.comments.length > 0) {
                ticket.comments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.className = 'comment';
                    commentElement.innerHTML = `
                        <div class="comment-header">
                            <span class="comment-author">${comment.author}</span>
                            <span class="comment-date">${comment.date}</span>
                        </div>
                        <div class="comment-text">${comment.text}</div>
                    `;
                    commentsContainer.appendChild(commentElement);
                });
            } else {
                commentsContainer.innerHTML = '<p>No comments yet.</p>';
            }
            
            // Reset new comment textarea
            document.getElementById('new-comment').innerHTML = '';
            
            // Open the modal
            openModal(ticketDetailModal);
        }

        // Enable edit mode for ticket
        function enableEditMode() {
            alert('Edit functionality would open a form to modify ticket details.');
            // In a real application, this would convert the detail view to editable form fields
        }

        // Approve a pending ticket
        function approveTicket() {
            const ticketId = parseInt(document.getElementById('detail-id').textContent.replace('#', ''));
            const ticketIndex = tickets.findIndex(t => t.id === ticketId);
            
            if (ticketIndex !== -1) {
                tickets[ticketIndex].status = 'open';
                renderTickets();
                approveTicketBtn.style.display = 'none';
                document.getElementById('detail-status').textContent = 'Open';
                
                alert('Ticket approved! It is now open for comments.');
            }
        }

        // Add a comment to the ticket
        function addComment() {
            const commentText = document.getElementById('new-comment').innerHTML;
            
            if (!commentText || commentText === '<br>') {
                alert('Please enter a comment');
                return;
            }
            
            const ticketId = parseInt(document.getElementById('detail-id').textContent.replace('#', ''));
            const ticketIndex = tickets.findIndex(t => t.id === ticketId);
            
            if (ticketIndex !== -1) {
                const newComment = {
                    author: 'Current User',
                    date: 'Just now',
                    text: commentText
                };
                
                if (!tickets[ticketIndex].comments) {
                    tickets[ticketIndex].comments = [];
                }
                
                tickets[ticketIndex].comments.push(newComment);
                
                // Update the UI
                const commentsContainer = document.getElementById('comments-container');
                const commentElement = document.createElement('div');
                commentElement.className = 'comment';
                commentElement.innerHTML = `
                    <div class="comment-header">
                        <span class="comment-author">${newComment.author}</span>
                        <span class="comment-date">${newComment.date}</span>
                    </div>
                    <div class="comment-text">${newComment.text}</div>
                `;
                commentsContainer.appendChild(commentElement);
                
                // Clear the editor
                document.getElementById('new-comment').innerHTML = '';
                
                // If the ticket was pending, approve it automatically when an admin comments
                if (tickets[ticketIndex].status === 'pending') {
                    tickets[ticketIndex].status = 'open';
                    renderTickets();
                    approveTicketBtn.style.display = 'none';
                    document.getElementById('detail-status').textContent = 'Open';
                }
            }
        }

        // Initialize the application
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>