import { mount } from '@vue/test-utils';
import TaskList from '@/components/TaskList.vue';
import axios from 'axios';

vi.mock('axios');

const user = { id: 1, role: 'user' };

describe('TaskList.vue', () => {

  beforeEach(() => {
    localStorage.clear();
  });

  test('fetches tasks on mount', async () => {
    axios.get.mockResolvedValue({ data: [{ id: 1, title: 'Task 1' }] });

    const wrapper = mount(TaskList, { props: { user } });
    await wrapper.vm.fetchTasks();

    expect(wrapper.vm.tasks.some(t => t.title === 'Task 1')).toBe(true);
  });

  test('adds a new task', async () => {
    axios.post.mockResolvedValue({ data: { id: 2, title: 'New Task' } });

    const wrapper = mount(TaskList, { props: { user } });
    await wrapper.setData({ newTask: 'New Task' });
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.tasks.some(t => t.title === 'New Task')).toBe(true);
  });

  test('updates a task', async () => {
    axios.put.mockResolvedValue({ data: { id: 1, title: 'Updated Task', completed: true, user_id: 1 } });

    const wrapper = mount(TaskList, { props: { user } });
    wrapper.setData({ tasks: [{ id: 1, title: 'Old Task', completed: false, user_id: 1 }] });
    await wrapper.vm.updateTask(wrapper.vm.tasks[0]);

    expect(wrapper.vm.tasks[0].title).toBe('Old Task'); // UI updated locally
  });

  test('deletes a task', async () => {
    axios.delete.mockResolvedValue({});

    const wrapper = mount(TaskList, { props: { user } });
    wrapper.setData({ tasks: [{ id: 1, title: 'Task 1', completed: false, user_id: 1 }] });
    await wrapper.vm.deleteTask(1);

    expect(wrapper.vm.tasks.length).toBe(0);
  });

  test('logs out the user', async () => {
    const wrapper = mount(TaskList, { props: { user } });
    await wrapper.vm.logout();

    expect(localStorage.getItem('token')).toBeNull();
    expect(wrapper.emitted('logout')).toBeTruthy();
  });

  test('does not add empty task', async () => {
    const wrapper = mount(TaskList, { props: { user } });
    await wrapper.setData({ newTask: '' });
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.tasks.length).toBe(0);
  });

  test('shows admin view for admin user', async () => {
    const adminUser = { id: 2, role: 'admin' };
    const wrapper = mount(TaskList, { props: { user: adminUser } });

    wrapper.setData({ tasks: [{ id: 1, title: 'Task 1', completed: false, user_id: 1 }] });
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('Admin view: You can delete any task');
  });

  test('handles API errors gracefully', async () => {
    axios.get.mockRejectedValue(new Error('API error'));

    const wrapper = mount(TaskList, { props: { user } });
    await wrapper.vm.fetchTasks();

    expect(wrapper.vm.tasks.length).toBe(0);
  });
});
