/* assets/js/wsts-ticket-list.js */

/**
 * Woo Support Ticket System - Frontend JavaScript
 * Handles ticket list rendering, filtering, modals, and AJAX interactions
 */
(function($) {
    'use strict';

    // Initialize ticket data from localized script
    const tickets = window.wsts_ajax.tickets || [];
    const isAdmin = window.wsts_ajax.is_admin;
    const currentUserId = <?php echo get_current_user_id(); ?>; // Added via PHP

    // DOM Elements
    const elements = {
        newTicketBtn: document.getElementById('wsts_new-ticket-btn'),
        headerNewTicketBtn: document.getElementById('wsts_header-new-ticket'),
        newTicketModal: document.getElementById('wsts_new-ticket-modal'),
        ticketDetailModal: document.getElementById('wsts_ticket-detail-modal'),
        closeButtons: document.querySelectorAll('.wsts_close-btn'),
        cancelNewTicketBtn: document.getElementById('wsts_cancel-new-ticket'),
        submitNewTicketBtn: document.getElementById('wsts_submit-new-ticket'),
        closeTicketDetailsBtn: document.getElementById('wsts_close-ticket-details'),
        editTicketBtn: document.getElementById('wsts_edit-ticket-btn'),
        approveTicketBtn: document.getElementById('wsts_approve-ticket-btn'),
        ticketsTableBody: document.getElementById('wsts_tickets-table-body'),
        newTicketForm: document.getElementById('wsts_new-ticket-form'),
        editorButtons: document.querySelectorAll('.wsts_editor-header button'),
        filterButton: document.querySelector('.wsts_filters button'),
        statusFilter: document.getElementById('wsts_status'),
        priorityFilter: document.getElementById('wsts_priority'),
        dateFilter: document.getElementById('wsts_date'),
        searchFilter: document.getElementById('wsts_search'),
        addCommentBtn: document.getElementById('wsts_add-comment-btn')
    };

    /**
     * Initialize the application
     */
    function init() {
        renderTickets();
        setupEventListeners();
        setupEditor();
    }

    /**
     * Render tickets to the table with optional filtering
     * @param {Object} filter - Filter criteria (status, priority, date, search)
     */
    function renderTickets(filter = {}) {
        elements.ticketsTableBody.innerHTML = '';
        let filteredTickets = tickets;

        // Apply user-specific filtering
        if (!isAdmin) {
            filteredTickets = filteredTickets.filter(t => t.owner_id === currentUserId);
        }

        // Apply additional filters
        if (filter.status && filter.status !== 'all') {
            filteredTickets = filteredTickets.filter(t => t.status === filter.status);
        }
        if (filter.priority && filter.priority !== 'all') {
            filteredTickets = filteredTickets.filter(t => t.priority === filter.priority);
        }
        if (filter.search) {
            const searchLower = filter.search.toLowerCase();
            filteredTickets = filteredTickets.filter(t => 
                t.subject.toLowerCase().includes(searchLower) || 
                t.description.toLowerCase().includes(searchLower)
            );
        }
        if (filter.date) {
            filteredTickets = filteredTickets.filter(t => {
                const createdDate = new Date(t.created.split(' ago')[0] + ' ago');
                return createdDate.toISOString().split('T')[0] === filter.date;
            });
        }

        // Paginate filtered tickets
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = parseInt(urlParams.get('tpage') || '1');
        const perPage = 5;
        const paginatedTickets = filteredTickets.slice((currentPage - 1) * perPage, currentPage * perPage);

        if (paginatedTickets.length === 0) {
            elements.ticketsTableBody.innerHTML = '<tr><td colspan="7">No tickets found.</td></tr>';
            return;
        }

        paginatedTickets.forEach(ticket => {
            const row = document.createElement('tr');
            row.dataset.id = ticket.id;
            row.innerHTML = `
                <td>#${ticket.id}</td>
                <td>${ticket.subject}</td>
                <td>${ticket.requester}</td>
                <td><span class="wsts_status wsts_status-${ticket.status}">${ticket.status_name}</span></td>
                <td class="wsts_priority-${ticket.priority}">${ticket.priority_name}</td>
                <td>${ticket.created}</td>
                <td><button class="wsts_action-btn" data-id="${ticket.id}"><i class="fas fa-eye"></i></button></td>
            `;
            elements.ticketsTableBody.appendChild(row);
        });

        // Update pagination
        const totalPages = Math.ceil(filteredTickets.length / perPage);
        const pagination = document.querySelector('.wsts_pagination');
        pagination.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.className = i === currentPage ? 'wsts_active' : '';
            button.addEventListener('click', () => {
                window.location.href = `?tpage=${i}`;
            });
            pagination.appendChild(button);
        }
    }

    /**
     * Setup all event listeners
     */
    function setupEventListeners() {
        // Open new ticket modal
        elements.newTicketBtn.addEventListener('click', () => openModal(elements.newTicketModal));
        elements.headerNewTicketBtn.addEventListener('click', () => openModal(elements.newTicketModal));

        // Close modals
        elements.closeButtons.forEach(button => {
            button.addEventListener('click', closeModals);
        });
        elements.cancelNewTicketBtn.addEventListener('click', closeModals);
        elements.closeTicketDetailsBtn.addEventListener('click', closeModals);

        // Submit new ticket
        elements.submitNewTicketBtn.addEventListener('click', createNewTicket);

        // Edit ticket
        elements.editTicketBtn.addEventListener('click', enableEditMode);

        // Approve ticket
        if (wsts_ajax.is_admin) {
            elements.approveTicketBtn.addEventListener('click', approveTicket);
        }

        // Add comment
        elements.addCommentBtn.addEventListener('click', addComment);

        // Click on ticket row to view details
        elements.ticketsTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('.wsts_action-btn');
            if (btn) {
                const ticketId = parseInt(btn.dataset.id);
                getSingleTicketHtml(ticketId);
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === elements.newTicketModal || e.target === elements.ticketDetailModal) {
                closeModals();
            }
        });

        // Apply filters
        elements.filterButton.addEventListener('click', () => {
            const filter = {
                status: elements.statusFilter.value,
                priority: elements.priorityFilter.value,
                date: elements.dateFilter.value,
                search: elements.searchFilter.value
            };
            renderTickets(filter);
        });

        // Handle product select visibility
        document.getElementById('wsts_ticket-type').addEventListener('change', function() {
            const wrapper = document.getElementById('wsts_product-select-wrapper');
            const select = document.getElementById('wsts_ticket-product');
            if (this.value === 'product') {
                wrapper.style.display = 'block';
                if (select.options.length === 1) {
                    $.ajax({
                        url: wsts_ajax.ajax_url,
                        type: 'GET',
                        data: { action: 'wsts_get_user_products', nonce: wsts_ajax.nonce },
                        success: function(resp) {
                            if (resp.success) {
                                select.innerHTML = '<option value="">Select a product</option>';
                                resp.data.forEach(p => {
                                    const option = new Option(p.name, p.id);
                                    select.appendChild(option);
                                });
                            }
                        }
                    });
                }
            } else {
                wrapper.style.display = 'none';
            }
        });
    }

    /**
     * Setup contenteditable editor functionality
     */
    function setupEditor() {
        elements.editorButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const command = button.dataset.command;
                const value = button.dataset.value || null;
                document.execCommand(command, false, value);
                document.querySelector('.wsts_editor-content:focus')?.focus();
            });
        });
    }

    /**
     * Open a modal
     * @param {HTMLElement} modal - The modal element to open
     */
    function openModal(modal) {
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    /**
     * Close all modals and reset form
     */
    function closeModals() {
        elements.newTicketModal.style.display = 'none';
        elements.ticketDetailModal.style.display = 'none';
        document.getElementById('wsts_subject').value = '';
        document.getElementById('wsts_ticket-type').value = 'general';
        document.getElementById('wsts_ticket-product').value = '';
        document.getElementById('wsts_priority').value = '';
        tinymce.get('wsts_description').setContent(''); // Use TinyMCE for description
        tinymce.get('wsts_new-comment').setContent(''); // Use TinyMCE for comment
        document.getElementById('wsts_form-notice').style.display = 'none';
    }

    /**
     * Create a new ticket via AJAX
     */
    function createNewTicket() {
        const subject = document.getElementById('wsts_subject').value;
        const type = document.getElementById('wsts_ticket-type').value;
        const product_id = type === 'product' ? document.getElementById('wsts_ticket-product').value : '';
        const priority = document.getElementById('wsts_priority').value;
        const description = tinymce.get('wsts_description').getContent(); // Use TinyMCE content
        const notice = document.getElementById('wsts_form-notice');

        if (!subject || !type || !priority || !description || description === '<p></p>') {
            notice.textContent = 'Please fill in all fields with valid content';
            notice.style.display = 'block';
            return;
        }

        $.post(wsts_ajax.ajax_url, {
            action: 'wsts_create_new_ticket',
            nonce: wsts_ajax.nonce,
            subject: subject,
            type: type,
            product_id: product_id,
            priority: priority,
            description: description
        }, function(resp) {
            if (resp.success) {
                closeModals();
                getSingleTicketHtml(resp.data.ticket_id);
                alert('Ticket created successfully! It is now pending admin approval.');
                window.location.reload();
            } else {
                notice.textContent = resp.data.message;
                notice.style.display = 'block';
            }
        });
    }

    /**
     * Fetch and display ticket details
     * @param {number} ticketId - The ID of the ticket to display
     */
    function getSingleTicketHtml(ticketId) {
        if (!ticketId) {
            console.error('Invalid ticket ID:', ticketId);
            return;
        }

        $.post(wsts_ajax.ajax_url, {
            action: 'wsts_get_single_ticket_html',
            nonce: wsts_ajax.nonce,
            ticket_id: ticketId
        }, function(resp) {
            if (resp.success) {
                const ticket = tickets.find(t => t.id === ticketId);
                if (!ticket) {
                    console.error('Ticket not found in local data:', ticketId);
                    return;
                }

                // Check if normal user is trying to view someone else's ticket
                if (!isAdmin && ticket.owner_id !== currentUserId) {
                    alert('You do not have permission to view this ticket.');
                    closeModals();
                    return;
                }

                document.getElementById('wsts_ticket-modal-title').textContent = `Ticket #${ticketId}`;
                document.getElementById('wsts_detail-id').textContent = `#${ticketId}`;
                document.getElementById('wsts_detail-subject').textContent = ticket.subject;
                document.getElementById('wsts_detail-requester').textContent = ticket.requester;
                document.getElementById('wsts_detail-department').textContent = ticket.department.charAt(0).toUpperCase() + ticket.department.slice(1);
                document.getElementById('wsts_detail-priority').textContent = ticket.priority_name;
                document.getElementById('wsts_detail-status').textContent = ticket.status_name;
                document.getElementById('wsts_detail-created').textContent = ticket.created;
                document.getElementById('wsts_detail-description').innerHTML = ticket.description;

                // Show/hide approve button
                if (ticket.status === 'pending' && wsts_ajax.is_admin) {
                    elements.approveTicketBtn.style.display = 'block';
                } else {
                    elements.approveTicketBtn.style.display = 'none';
                }

                // Render comments with admin response indication
                const commentsContainer = document.getElementById('wsts_comments-container');
                commentsContainer.innerHTML = '';
                if (ticket.comments && ticket.comments.length > 0) {
                    ticket.comments.forEach(comment => {
                        const isAdminComment = comment.user_id && user_can( comment.user_id, 'manage_options' );
                        const commentElement = document.createElement('div');
                        commentElement.className = 'wsts_comment';
                        commentElement.innerHTML = `
                            <div class="wsts_comment-header">
                                <span class="wsts_comment-author">${comment.comment_author} ${isAdminComment ? '(Admin)' : ''}</span>
                                <span class="wsts_comment-date">${new Date(comment.comment_date).toLocaleString()}</span>
                            </div>
                            <div class="wsts_comment-text">${comment.comment_content}</div>
                        `;
                        commentsContainer.appendChild(commentElement);
                    });
                } else {
                    commentsContainer.innerHTML = '<p>No comments yet.</p>';
                }

                // Reset comment editor
                tinymce.get('wsts_new-comment').setContent('');

                openModal(elements.ticketDetailModal);
            } else {
                console.error('AJAX error for ticket ID:', ticketId, resp.data);
                alert('Failed to load ticket details. Please try again.');
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX request failed:', status, error);
            alert('An error occurred while loading ticket details.');
        });
    }

    /**
     * Enable edit mode for ticket (placeholder)
     */
    function enableEditMode() {
        alert('Edit functionality would open a form to modify ticket details.');
    }

    /**
     * Approve a pending ticket via AJAX
     */
    function approveTicket() {
        const ticketId = parseInt(document.getElementById('wsts_detail-id').textContent.replace('#', ''));
        $.post(wsts_ajax.ajax_url, {
            action: 'wsts_approve_ticket',
            nonce: wsts_ajax.nonce,
            ticket_id: ticketId
        }, function(resp) {
            if (resp.success) {
                const ticketIndex = tickets.findIndex(t => t.id === ticketId);
                if (ticketIndex !== -1) {
                    tickets[ticketIndex].status = 'open';
                    tickets[ticketIndex].status_name = 'Open';
                    renderTickets();
                    document.getElementById('wsts_detail-status').textContent = 'Open';
                    elements.approveTicketBtn.style.display = 'none';
                    alert('Ticket approved! It is now open for comments.');
                }
            } else {
                alert('Error approving ticket.');
            }
        });
    }

    /**
     * Add a comment to the ticket via AJAX
     */
    function addComment() {
        const commentText = tinymce.get('wsts_new-comment').getContent();
        const ticketId = parseInt(document.getElementById('wsts_detail-id').textContent.replace('#', ''));

        if (!commentText || commentText === '<p></p>') {
            alert('Please enter a comment');
            return;
        }

        // Check if the user has permission to comment on this ticket
        const ticket = tickets.find(t => t.id === ticketId);
        if (!isAdmin && ticket.owner_id !== currentUserId) {
            alert('You do not have permission to comment on this ticket.');
            return;
        }

        $.post(wsts_ajax.ajax_url, {
            action: 'wsts_add_comment',
            nonce: wsts_ajax.nonce,
            comment_post_ID: ticketId,
            comment: commentText,
            comment_parent: 0
        }, function(resp) {
            if (resp.success) {
                getSingleTicketHtml(ticketId);
                if (wsts_ajax.is_admin && ticket.status === 'pending') {
                    approveTicket();
                }
            } else {
                alert('Error adding comment.');
            }
        });
    }

    // Initialize on DOM load
    document.addEventListener('DOMContentLoaded', init);
})(jQuery);