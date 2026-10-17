import { Form, Head } from '@inertiajs/react';
import {
    capsButton,
    Field,
    SelectInput,
    TextInput,
} from '@/components/library/form-controls';
import { LedgerTable, Td, Th } from '@/components/library/ledger-table';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { Pagination } from '@/components/library/pagination';
import { Button } from '@/components/ui/button';
import { formatDateTime } from '@/lib/library';
import { index } from '@/routes/logs';
import type { ActivityLog, Paginated, Role } from '@/types';

const roleLabels: Record<Role, string> = {
    admin: 'Admin',
    librarian: 'Librarian',
    borrower: 'Reader',
};

export default function LogsIndex({
    logs,
    users,
    filters,
}: {
    logs: Paginated<ActivityLog>;
    users: { id: number; name: string }[];
    filters: { user?: string; from?: string; to?: string };
}) {
    return (
        <>
            <Head title="Activity logs" />
            <PageBody>
                <PageHeader
                    title="Activity logs"
                    description="Every sign-in and change, newest first. Entries cannot be edited or deleted."
                />

                <Form
                    {...index.form()}
                    options={{ preserveState: true }}
                    className="flex flex-wrap items-end gap-3.5"
                >
                    <Field
                        label="Person"
                        htmlFor="user"
                        className="flex-[0_1_240px]"
                    >
                        <SelectInput
                            id="user"
                            name="user"
                            defaultValue={filters.user ?? ''}
                        >
                            <option value="">Everyone</option>
                            {users.map((user) => (
                                <option key={user.id} value={user.id}>
                                    {user.name}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>
                    <Field
                        label="From"
                        htmlFor="from"
                        className="flex-[0_1_190px]"
                    >
                        <TextInput
                            id="from"
                            name="from"
                            type="date"
                            defaultValue={filters.from}
                        />
                    </Field>
                    <Field label="To" htmlFor="to" className="flex-[0_1_190px]">
                        <TextInput
                            id="to"
                            name="to"
                            type="date"
                            defaultValue={filters.to}
                        />
                    </Field>
                    <Button
                        type="submit"
                        variant="outline"
                        size="lg"
                        className={`${capsButton} h-11`}
                    >
                        Filter
                    </Button>
                </Form>

                <LedgerTable minWidth={680}>
                    <thead>
                        <tr>
                            <Th className="w-44">When</Th>
                            <Th className="w-64">Who</Th>
                            <Th>What happened</Th>
                        </tr>
                    </thead>
                    <tbody>
                        {logs.data.map((log) => (
                            <tr key={log.id}>
                                <Td className="whitespace-nowrap text-dust tabular-nums">
                                    {formatDateTime(log.created_at)}
                                </Td>
                                <Td className="whitespace-nowrap">
                                    <span className="font-semibold">
                                        {log.user?.name ?? 'Deleted user'}
                                    </span>
                                    {log.user && (
                                        <span className="ml-2 font-caps text-[11px] font-semibold tracking-[0.1em] text-faded uppercase">
                                            {roleLabels[log.user.role]}
                                        </span>
                                    )}
                                </Td>
                                <Td className="text-bone-soft">{log.action}</Td>
                            </tr>
                        ))}
                    </tbody>
                </LedgerTable>

                <Pagination page={logs} noun="entries" />
            </PageBody>
        </>
    );
}

LogsIndex.layout = {
    breadcrumbs: [{ title: 'Activity Logs', href: index() }],
};
