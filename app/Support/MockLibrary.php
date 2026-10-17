<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * TEMPORARY sample data for the front end, used only by routes/mock-pages.php.
 *
 * Each array has the same shape a real controller will send, so the pages keep
 * working when you swap a mock route for its controller. Delete this file once
 * every controller is built.
 */
class MockLibrary
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function categories(): array
    {
        $rows = [
            [1, 'Greek Mythology', 'Gods, heroes and monsters of ancient Greece, and the novels that retell them.', 18],
            [2, 'Norse Mythology', 'Odin, Thor, Loki and the nine worlds.', 9],
            [3, 'Roman Mythology', 'Rome’s gods and founding legends.', 6],
            [4, 'Egyptian Mythology', 'Ra, Osiris, Isis and the journey through the afterlife.', 7],
            [5, 'Philippine Mythology', 'Bathala, the diwata and the creatures of our own folklore.', 5],
        ];

        return array_map(fn (array $row) => [
            'id' => $row[0],
            'name' => $row[1],
            'description' => $row[2],
            'books_count' => $row[3],
            'created_at' => '2026-08-01T08:00:00.000000Z',
            'updated_at' => '2026-08-01T08:00:00.000000Z',
        ], $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function books(): array
    {
        $cover = fn (int $id) => "https://covers.openlibrary.org/b/id/{$id}-M.jpg";

        // id, title, author, isbn, year, status, category id, cover
        $rows = [
            [1, 'Mythology', 'Edith Hamilton', '9780316438520', 1942, 'available', 1, $cover(8765912)],
            [2, 'Mythos', 'Stephen Fry', '9781405934138', 2017, 'available', 1, $cover(8238803)],
            [3, 'The Greek Myths', 'Robert Graves', '9780143106715', 1955, 'borrowed', 1, $cover(5415692)],
            [4, 'Circe', 'Madeline Miller', '9780316556347', 2018, 'available', 1, $cover(8739376)],
            [5, 'The Song of Achilles', 'Madeline Miller', '9780062060624', 2011, 'borrowed', 1, $cover(7098465)],
            [6, 'The Odyssey', 'Homer, tr. Emily Wilson', '9780393356250', 2017, 'available', 1, $cover(8100018)],
            [7, 'Norse Mythology', 'Neil Gaiman', '9780393356182', 2017, 'available', 2, $cover(7898682)],
            [8, 'The Prose Edda', 'Snorri Sturluson', '9780140447552', 1220, 'borrowed', 2, $cover(6857263)],
            [9, 'Metamorphoses', 'Ovid', '9780140447897', 8, 'available', 3, $cover(7212118)],
            [10, 'The Aeneid', 'Virgil', '9780143106289', 19, 'borrowed', 3, $cover(6715695)],
            [11, 'The Egyptian Book of the Dead', 'E. A. Wallis Budge', '9780140455502', 1895, 'available', 4, $cover(12632615)],
            [12, 'Philippine Folk Literature: The Myths', 'Damiana L. Eugenio', '9789715421218', 1993, 'available', 5, $cover(4927889)],
        ];

        $categories = collect(self::categories())->keyBy('id');

        return array_map(fn (array $row) => [
            'id' => $row[0],
            'category_id' => $row[6],
            'title' => $row[1],
            'author' => $row[2],
            'isbn' => $row[3],
            'publication_year' => $row[4],
            'availability_status' => $row[5],
            'cover_url' => $row[7],
            'category' => $categories[$row[6]],
            'created_at' => '2026-08-01T08:00:00.000000Z',
            'updated_at' => '2026-08-01T08:00:00.000000Z',
        ], $rows);
    }

    /**
     * @return array<string, mixed>
     */
    public static function book(int $id): array
    {
        return collect(self::books())->firstWhere('id', $id) ?? self::books()[0];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function members(): array
    {
        // id, name, contact, address, has an account, books out
        $rows = [
            [1, 'Juan Dela Cruz', '0917 555 0123', 'Brgy. San Antonio, Pasig City', true, 2],
            [2, 'Bea Villanueva', '0917 555 0188', 'Sampaloc, Manila', true, 2],
            [3, 'Carlo Mendoza', '0917 555 0142', 'Brgy. Kapitolyo, Pasig City', true, 3],
            [4, 'Lea Castillo', '0918 555 0199', 'Marikina Heights, Marikina', true, 1],
            [5, 'Paolo Garcia', '0927 555 0110', 'Project 4, Quezon City', false, 1],
            [6, 'Miguel Santos', '0915 555 0176', 'Malate, Manila', true, 0],
            [7, 'Rosa Aquino', '0919 555 0134', 'Brgy. Ugong, Valenzuela', false, 0],
        ];

        return array_map(fn (array $row) => [
            'id' => $row[0],
            'user_id' => $row[4] ? $row[0] + 10 : null,
            'name' => $row[1],
            'contact_number' => $row[2],
            'address' => $row[3],
            'active_borrowings_count' => $row[5],
            'user' => $row[4] ? [
                'id' => $row[0] + 10,
                'name' => $row[1],
                'username' => strtolower(str_replace(' ', '', $row[1])),
                'email' => strtolower(str_replace(' ', '.', $row[1])).'@athenaeum.test',
                'role' => 'borrower',
            ] : null,
            'created_at' => '2026-08-12T08:00:00.000000Z',
            'updated_at' => '2026-08-12T08:00:00.000000Z',
        ], $rows);
    }

    /**
     * @return array<string, mixed>
     */
    public static function member(int $id): array
    {
        return collect(self::members())->firstWhere('id', $id) ?? self::members()[0];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function borrowings(): array
    {
        $today = Carbon::today();
        $books = collect(self::books())->keyBy('id');
        $members = collect(self::members())->keyBy('id');

        // id, book, member, status, borrowed (days ago), due (days from today), returned (days ago)
        $rows = [
            [1042, 4, 1, 'pending', null, null, null],
            [1041, 7, 2, 'pending', null, null, null],
            [1040, 12, 3, 'pending', null, null, null],
            [1039, 6, 6, 'pending', null, null, null],
            [1038, 3, 1, 'borrowed', 7, 7, null],
            [1036, 8, 4, 'borrowed', 9, 5, null],
            [1031, 5, 3, 'borrowed', 22, -8, null],
            [1029, 10, 5, 'borrowed', 17, -3, null],
            [1027, 1, 7, 'returned', 27, -13, 16],
            [1025, 7, 1, 'returned', 29, -15, 17],
            [1024, 9, 1, 'rejected', null, null, null],
        ];

        return array_map(fn (array $row) => [
            'id' => $row[0],
            'book_id' => $row[1],
            'member_id' => $row[2],
            'status' => $row[3],
            'borrow_date' => $row[4] === null ? null : $today->copy()->subDays($row[4])->toDateString(),
            'due_date' => $row[5] === null ? null : $today->copy()->addDays($row[5])->toDateString(),
            'return_date' => $row[6] === null ? null : $today->copy()->subDays($row[6])->toDateString(),
            'book' => $books[$row[1]],
            'member' => $members[$row[2]],
            'created_at' => $today->copy()->subDays($row[4] ?? 1)->setTime(8, 42)->toIso8601String(),
            'updated_at' => $today->copy()->toIso8601String(),
        ], $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function users(): array
    {
        $rows = [
            [1, 'Maria Santos', 'admin', 'admin@library.test', 'admin'],
            [2, 'Ana Reyes', 'librarian', 'librarian@library.test', 'librarian'],
            [4, 'Liza Tan', 'lizatan', 'liza.tan@athenaeum.test', 'librarian'],
            [3, 'Juan Dela Cruz', 'borrower', 'borrower@library.test', 'borrower'],
            [5, 'Bea Villanueva', 'beav', 'bea.villanueva@gmail.com', 'borrower'],
            [6, 'Carlo Mendoza', 'carlom', 'carlo.mendoza@yahoo.com', 'borrower'],
        ];

        return array_map(fn (array $row) => [
            'id' => $row[0],
            'name' => $row[1],
            'username' => $row[2],
            'email' => $row[3],
            'role' => $row[4],
            'email_verified_at' => '2026-08-01T08:00:00.000000Z',
            'created_at' => '2026-08-01T08:00:00.000000Z',
            'updated_at' => '2026-08-01T08:00:00.000000Z',
        ], $rows);
    }

    /**
     * @return array<string, mixed>
     */
    public static function user(int $id): array
    {
        return collect(self::users())->firstWhere('id', $id) ?? self::users()[0];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function logs(): array
    {
        $users = collect(self::users())->keyBy('id');
        $today = Carbon::today();

        $rows = [
            [2, '09:12', 'Approved borrowing 1038: The Greek Myths for Juan Dela Cruz'],
            [1, '09:05', 'Created user Liza Tan as Librarian'],
            [2, '08:58', 'Marked borrowing 1027 returned: Mythology'],
            [2, '08:51', 'Added book Mythos'],
            [3, '08:42', 'Requested Circe (borrowing 1042)'],
            [3, '08:40', 'Signed in'],
            [2, '08:03', 'Edited category Greek Mythology'],
            [2, '08:01', 'Signed in'],
            [1, '07:55', 'Signed in'],
        ];

        return array_map(fn (array $row, int $index) => [
            'id' => 100 - $index,
            'user_id' => $row[0],
            'action' => $row[2],
            'user' => $users[$row[0]],
            'created_at' => $today->copy()->setTimeFromTimeString($row[1])->toIso8601String(),
            'updated_at' => $today->copy()->setTimeFromTimeString($row[1])->toIso8601String(),
        ], $rows, array_keys($rows));
    }

    /**
     * Wrap an array in Laravel's real paginator, so pages get the same shape
     * as ->paginate() in a controller.
     *
     * @param  array<int, mixed>  $items
     * @return array<string, mixed>
     */
    public static function paginate(array $items, Request $request, int $perPage = 10): array
    {
        $page = max(1, (int) $request->query('page', 1));

        return (new LengthAwarePaginator(
            array_slice($items, ($page - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        ))->toArray();
    }
}
