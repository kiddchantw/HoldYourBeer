# Tasting History Page Design

## 1. Feature Overview

This feature allows users to view a detailed history of their tasting activities for a specific beer. It provides a timeline view of all tasting events, including the initial addition of the beer, subsequent increments, and any notes associated with each event.

## 2. User Flow

The user flow for accessing the tasting history is as follows:

1.  The user navigates to the main dashboard.
2.  The user clicks on a specific beer card in their collection.
3.  The application directs the user to the tasting history page for the selected beer.

This flow is visualized in `diagrams/flow-user-view-tasting-history.md`.

## 3. UI Components

The tasting history page will consist of the following UI elements:

*   **Header**: Displays the full name of the beer (e.g., "Kirin Lager").
*   **Timeline**: A vertically oriented timeline that displays the tasting events in chronological order (newest first).
*   **Timeline Entry**: Each entry in the timeline represents a single tasting event and includes:
    *   **Action Type**: The type of event (e.g., "Initial", "Increment").
    *   **Timestamp**: The date and time of the event (e.g., "2025-08-21 18:30").
    *   **Note**: Any user-added notes for the event. If no note is present, this section is omitted.

## 4. Data Requirements

To render the tasting history page, the following data is required:

*   **Beer Information**:
    *   Brand Name
    *   Beer Name
*   **Tasting History**:
    *   A collection of tasting log entries, each containing:
        *   `action` (string)
        *   `tasted_at` (datetime)
        *   `note` (string, nullable)
