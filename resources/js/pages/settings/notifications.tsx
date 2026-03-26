import { Form, Head } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import NotificationController from '@/actions/App/Http/Controllers/Settings/NotificationController';

type NotificationSetting = {
    type: string;
    label: string;
    in_app: boolean;
    push: boolean;
    enabled: boolean;
};

type Props = {
    notifications: NotificationSetting[];
};

export default function Notifications({ notifications: initialNotifications }: Props) {
    return (
        <>
            <Head title="Notifications" />
            <h1 className="sr-only">Notifications</h1>
            <div className="space-y-6">
                <Heading variant="small" title="Notifications" description="Choose which notifications you receive." />
                <Form
                    {...NotificationController.update.form({ notifications: initialNotifications })}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, recentlySuccessful, data, setData }) => {
                        const notifications = data.notifications as NotificationSetting[];

                        const toggleField = (index: number, field: 'in_app' | 'push' | 'enabled') => {
                            const updated = [...notifications];
                            updated[index] = { ...updated[index], [field]: !updated[index][field] };
                            setData('notifications', updated);
                        };

                        return (
                            <>
                                <div className="space-y-4">
                                    {notifications.map((pref, index) => (
                                        <div key={pref.type} className="flex items-center justify-between rounded-lg border p-4">
                                            <div className="flex-1">
                                                <Label className="text-sm font-medium">{pref.label}</Label>
                                            </div>
                                            <div className="flex items-center gap-4">
                                                <label className="flex items-center gap-2 text-sm">
                                                    <input
                                                        type="checkbox"
                                                        checked={pref.enabled}
                                                        onChange={() => toggleField(index, 'enabled')}
                                                        className="h-4 w-4 rounded border-gray-300"
                                                    />
                                                    Enabled
                                                </label>
                                                <label className="flex items-center gap-2 text-sm">
                                                    <input
                                                        type="checkbox"
                                                        checked={pref.in_app}
                                                        onChange={() => toggleField(index, 'in_app')}
                                                        className="h-4 w-4 rounded border-gray-300"
                                                    />
                                                    In-app
                                                </label>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>Save</Button>
                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">Saved.</p>
                                    </Transition>
                                </div>
                            </>
                        );
                    }}
                </Form>
            </div>
        </>
    );
}
