// The longest value each field accepts, so the browser stops typing at the
// limit. Keep these in step with the Laravel rules (max:...) in the
// controllers, app/Concerns and app/Actions/Fortify.
export const LIMITS = {
    name: 100,
    username: 30,
    email: 255,
    password: 255,
    contactNumber: 20,
    address: 255,
    search: 100,
    title: 255,
    author: 255,
    isbn: 20,
    description: 500,
} as const;
