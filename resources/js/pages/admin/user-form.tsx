import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin-layout';

type UserData = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
};

type Props = {
    user?: UserData;
};

export default function AdminUserForm({ user }: Props) {
    const isEditing = !!user;

    const form = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        password: '',
        is_admin: user?.is_admin ?? false,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (isEditing) {
            form.put(`/admin/users/${user.id}`);
        } else {
            form.post('/admin/users');
        }
    }

    return (
        <>
            <Head title={`Admin - ${isEditing ? 'Edit' : 'Create'} User`} />
            <div className="flex flex-col gap-4">
                <div className="flex items-center gap-3">
                    <Link href="/admin/users" className="rounded-md p-1 hover:bg-muted">
                        <ArrowLeft className="h-5 w-5" />
                    </Link>
                    <h1 className="text-2xl font-bold">{isEditing ? 'Edit User' : 'Create User'}</h1>
                </div>

                <Card className="max-w-lg">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">{isEditing ? 'Update user details' : 'New user details'}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    value={form.data.name}
                                    onChange={(e) => form.setData('name', e.target.value)}
                                    placeholder="Full name"
                                    autoFocus
                                />
                                {form.errors.name && <p className="text-sm text-red-600">{form.errors.name}</p>}
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={form.data.email}
                                    onChange={(e) => form.setData('email', e.target.value)}
                                    placeholder="user@example.com"
                                />
                                {form.errors.email && <p className="text-sm text-red-600">{form.errors.email}</p>}
                            </div>

                            {!isEditing && (
                                <div className="flex flex-col gap-1.5">
                                    <Label htmlFor="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={form.data.password}
                                        onChange={(e) => form.setData('password', e.target.value)}
                                        placeholder="Minimum 8 characters"
                                    />
                                    {form.errors.password && <p className="text-sm text-red-600">{form.errors.password}</p>}
                                </div>
                            )}

                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="is_admin"
                                    checked={form.data.is_admin}
                                    onCheckedChange={(checked) => form.setData('is_admin', checked === true)}
                                />
                                <Label htmlFor="is_admin" className="cursor-pointer">Admin privileges</Label>
                            </div>
                            {form.errors.is_admin && <p className="text-sm text-red-600">{form.errors.is_admin}</p>}

                            <div className="flex gap-2 pt-2">
                                <Button type="submit" disabled={form.processing}>
                                    {form.processing ? 'Saving...' : isEditing ? 'Update User' : 'Create User'}
                                </Button>
                                <Link href="/admin/users">
                                    <Button type="button" variant="outline">Cancel</Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

AdminUserForm.layout = (page: React.ReactNode) => (
    <AdminLayout breadcrumbs={[{ title: 'Admin', href: '/admin' }, { title: 'Users', href: '/admin/users' }, { title: 'User Form' }]}>
        {page}
    </AdminLayout>
);
